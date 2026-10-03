<?php
/**
 * Accès aux données des véhicules.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Repositories;

use SierraLogistics\Schema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accès à wp_sierra_vehicles.
 */
class Vehicles_Repository {

	const STATUSES = array( 'disponible', 'en_course', 'maintenance' );

	/**
	 * Récupère un véhicule par son id.
	 *
	 * @param string $id UUID du véhicule.
	 */
	public function find( string $id ): ?array {
		global $wpdb;
		$table = Schema::vehicles_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- table personnalisée du plugin, pas d'API wp_* équivalente.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- {$table} est le nom de table interne (Schema::vehicles_table()), jamais une donnée utilisateur ; $wpdb->prepare() ne peut de toute façon pas paramétrer un identifiant.
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %s", $id ), ARRAY_A );
		return $row ? $row : null;
	}

	/**
	 * Liste les véhicules, avec filtre optionnel par statut.
	 *
	 * @param array $args Filtres optionnels (status).
	 */
	public function all( array $args = array() ): array {
		global $wpdb;
		$table = Schema::vehicles_table();

		$where  = array( '1=1' );
		$params = array();

		if ( ! empty( $args['status'] ) ) {
			$where[]  = 'status = %s';
			$params[] = $args['status'];
		}

		$sql = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where ) . ' ORDER BY created_at DESC';
		if ( $params ) {
			$sql = $wpdb->prepare( $sql, $params ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql est une chaîne littérale construite juste au-dessus (interpolation du nom de table uniquement, jamais de donnée utilisateur).
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- $table est un nom de table interne ; $sql est déjà préparé via $wpdb->prepare() ci-dessus quand il y a des paramètres, et ne contient aucune donnée utilisateur sinon.
		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Insère ou met à jour un véhicule avec un id imposé (import de données
	 * existantes : l'UUID Supabase d'origine est conservé tel quel, voir
	 * Importer::import_vehicles()). Idempotent : relancer avec le même id met
	 * juste à jour la ligne, sans créer de doublon.
	 *
	 * @param array $data Données complètes du véhicule, y compris 'id'.
	 */
	public function import_upsert( array $data ): void {
		global $wpdb;
		$table = Schema::vehicles_table();

		if ( $this->find( $data['id'] ) ) {
			$wpdb->update( $table, $data, array( 'id' => $data['id'] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- table personnalisée du plugin, $wpdb->update() échappe déjà les valeurs.
		} else {
			$wpdb->insert( $table, $data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- table personnalisée du plugin, $wpdb->insert() échappe déjà les valeurs.
		}
	}

	/**
	 * Crée un véhicule.
	 *
	 * @param array $data Données du véhicule (name, model, license_plate, fuel_type, status, contact_phone).
	 *
	 * @return string UUID du véhicule créé.
	 */
	public function create( array $data ): string {
		global $wpdb;
		$table = Schema::vehicles_table();

		$status = in_array( $data['status'] ?? 'disponible', self::STATUSES, true )
			? $data['status']
			: 'disponible';

		$id = wp_generate_uuid4();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- table personnalisée du plugin, $wpdb->insert() échappe déjà les valeurs.
		$wpdb->insert(
			$table,
			array(
				'id'            => $id,
				'name'          => $data['name'],
				'model'         => $data['model'],
				'license_plate' => $data['license_plate'],
				'fuel_type'     => $data['fuel_type'],
				'status'        => $status,
				'contact_phone' => $data['contact_phone'] ?? null,
				'created_at'    => gmdate( 'Y-m-d H:i:s' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return $id;
	}

	/**
	 * Met à jour un véhicule.
	 *
	 * @param string $id   UUID du véhicule.
	 * @param array  $data Colonnes à mettre à jour.
	 */
	public function update( string $id, array $data ): bool {
		global $wpdb;
		$table = Schema::vehicles_table();

		if ( isset( $data['status'] ) && ! in_array( $data['status'], self::STATUSES, true ) ) {
			unset( $data['status'] );
		}

		if ( empty( $data ) ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- table personnalisée du plugin, $wpdb->update() échappe déjà les valeurs.
		return false !== $wpdb->update( $table, $data, array( 'id' => $id ) );
	}

	/**
	 * Supprime un véhicule.
	 *
	 * @param string $id UUID du véhicule.
	 */
	public function delete( string $id ): bool {
		global $wpdb;
		$table = Schema::vehicles_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- table personnalisée du plugin, $wpdb->delete() échappe déjà les valeurs.
		return false !== $wpdb->delete( $table, array( 'id' => $id ), array( '%s' ) );
	}

	/**
	 * Réserve atomiquement le premier véhicule disponible parmi les candidats
	 * fournis (status 'disponible' -> 'en_course'). L'UPDATE conditionnel ne
	 * touche aucune ligne si un autre process a déjà pris le véhicule
	 * entre-temps, ce qui évite le double-booking sans verrou explicite. Port
	 * de claimVehicle() dans src/app/api/commandes/valider/route.js.
	 *
	 * @param string[] $vehicle_ids Candidats, dans l'ordre de priorité.
	 */
	public function claim_available( array $vehicle_ids ): ?array {
		global $wpdb;
		$table = Schema::vehicles_table();

		foreach ( $vehicle_ids as $vehicle_id ) {
			$sql = "UPDATE {$table} SET status = 'en_course' WHERE id = %s AND status = 'disponible'";
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- {$table} est un nom de table interne, jamais une donnée utilisateur ; $sql est une chaîne littérale assignée juste au-dessus.
			$updated = $wpdb->query( $wpdb->prepare( $sql, $vehicle_id ) );

			if ( $updated ) {
				return $this->find( $vehicle_id );
			}
		}

		return null;
	}

	/**
	 * Repasse un véhicule à "disponible".
	 *
	 * @param string $id UUID du véhicule.
	 */
	public function release( string $id ): bool {
		return $this->update( $id, array( 'status' => 'disponible' ) );
	}
}
