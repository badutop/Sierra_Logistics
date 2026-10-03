<?php
/**
 * Accès aux données des commandes.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Repositories;

use SierraLogistics\Schema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accès à wp_sierra_commandes.
 */
class Commandes_Repository {

	/**
	 * Récupère la commande associée à un devis.
	 *
	 * @param string $proforma_id UUID du devis.
	 */
	public function find_by_proforma( string $proforma_id ): ?array {
		global $wpdb;
		$table = Schema::commandes_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- table personnalisée du plugin.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- {$table} est un nom de table interne, jamais une donnée utilisateur.
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE proforma_id = %s", $proforma_id ), ARRAY_A );
		return $row ? $row : null;
	}

	/**
	 * Liste toutes les commandes, les plus récentes d'abord.
	 */
	public function all(): array {
		global $wpdb;
		$table = Schema::commandes_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- table personnalisée du plugin.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- {$table} est un nom de table interne, jamais une donnée utilisateur.
		return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY date_validation DESC", ARRAY_A );
	}

	/**
	 * Insère ou met à jour une commande avec un id imposé (import de données
	 * existantes : l'UUID Supabase d'origine est conservé tel quel, voir
	 * Importer::import_commandes()). Idempotent : relancer avec le même id
	 * met juste à jour la ligne, sans créer de doublon.
	 *
	 * @param array $data Données complètes de la commande, y compris 'id'.
	 */
	public function import_upsert( array $data ): void {
		global $wpdb;
		$table = Schema::commandes_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- table personnalisée du plugin.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- {$table} est un nom de table interne, jamais une donnée utilisateur.
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE id = %s", $data['id'] ) );

		if ( $exists ) {
			$wpdb->update( $table, $data, array( 'id' => $data['id'] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- table personnalisée du plugin, $wpdb->update() échappe déjà les valeurs.
		} else {
			$wpdb->insert( $table, $data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- table personnalisée du plugin, $wpdb->insert() échappe déjà les valeurs.
		}
	}

	/**
	 * Crée la commande. Si l'insertion échoue après que le véhicule a été
	 * réservé par Vehicles_Repository::claim_available(), le véhicule est
	 * relâché pour ne pas le laisser bloqué en "en_course" sans commande
	 * associée (parité avec src/app/api/commandes/valider/route.js).
	 *
	 * @param array               $data                Colonnes de la commande (proforma_id, vehicle_id,
	 *                                                  camion_immatriculation, chauffeur, telephone_chauffeur).
	 * @param Vehicles_Repository $vehicles_repository Pour relâcher le véhicule en cas d'échec.
	 *
	 * @return string|\WP_Error UUID de la commande créée, ou une erreur.
	 */
	public function create( array $data, Vehicles_Repository $vehicles_repository ) {
		global $wpdb;
		$table = Schema::commandes_table();

		$id = wp_generate_uuid4();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- table personnalisée du plugin, $wpdb->insert() échappe déjà les valeurs.
		$inserted = $wpdb->insert(
			$table,
			array(
				'id'                     => $id,
				'proforma_id'            => $data['proforma_id'],
				'vehicle_id'             => $data['vehicle_id'],
				'camion_immatriculation' => $data['camion_immatriculation'],
				'chauffeur'              => $data['chauffeur'],
				'telephone_chauffeur'    => $data['telephone_chauffeur'] ?? null,
				'invoice_number'         => \SierraLogistics\Invoice_Numbering::next_definitive(),
				'date_validation'        => gmdate( 'Y-m-d H:i:s' ),
			)
		);

		if ( false === $inserted ) {
			$vehicles_repository->release( $data['vehicle_id'] );
			return new \WP_Error( 'sierra_commande_insert_failed', $wpdb->last_error );
		}

		return $id;
	}
}
