<?php
/**
 * Accès aux données des devis.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Repositories;

use SierraLogistics\Schema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accès à wp_sierra_quotes.
 */
class Quotes_Repository {

	const STATUSES = array( 'en_attente', 'commandé' );

	/**
	 * Récupère un devis par son id.
	 *
	 * @param string $id UUID du devis.
	 */
	public function find( string $id ): ?array {
		global $wpdb;
		$table = Schema::quotes_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- table personnalisée du plugin.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- {$table} est un nom de table interne, jamais une donnée utilisateur.
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %s", $id ), ARRAY_A );
		return $row ? $row : null;
	}

	/**
	 * Recherche les devis d'un numéro de téléphone, les plus récents d'abord.
	 * Filtre côté serveur (contrairement à l'ancienne policy RLS Supabase qui
	 * exposait toute la table quotes à la clé anon, voir migration/AUDIT.md §2.3).
	 *
	 * @param string $telephone Numéro de téléphone recherché.
	 * @param int    $limit     Nombre maximum de résultats.
	 */
	public function find_by_telephone( string $telephone, int $limit = 1 ): array {
		global $wpdb;
		$table = Schema::quotes_table();
		$sql   = "SELECT * FROM {$table} WHERE telephone = %s ORDER BY created_at DESC LIMIT %d";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- {$table} est un nom de table interne, jamais une donnée utilisateur ; $sql est une chaîne littérale assignée juste au-dessus.
		return $wpdb->get_results( $wpdb->prepare( $sql, $telephone, $limit ), ARRAY_A );
	}

	/**
	 * Liste les devis, avec filtres optionnels (statut, recherche texte, dates).
	 *
	 * @param array $args Filtres optionnels (statut, search, date_from, date_to, limit, offset).
	 */
	public function all( array $args = array() ): array {
		global $wpdb;
		$table                  = Schema::quotes_table();
		list( $where, $params ) = self::build_filters( $args );

		$sql = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where ) . ' ORDER BY created_at DESC';

		if ( ! empty( $args['limit'] ) ) {
			$sql     .= ' LIMIT %d OFFSET %d';
			$params[] = (int) $args['limit'];
			$params[] = (int) ( $args['offset'] ?? 0 );
		}

		if ( $params ) {
			$sql = $wpdb->prepare( $sql, $params ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql est une chaîne littérale construite juste au-dessus (interpolation du nom de table uniquement, jamais de donnée utilisateur).
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- $table est un nom de table interne ; $sql est déjà préparé via $wpdb->prepare() ci-dessus quand il y a des paramètres, et ne contient aucune donnée utilisateur sinon.
		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Compte les devis correspondant aux mêmes filtres que all() (hors
	 * limit/offset), pour la pagination du back-office.
	 *
	 * @param array $args Filtres optionnels (statut, search, date_from, date_to).
	 */
	public function count( array $args = array() ): int {
		global $wpdb;
		$table                  = Schema::quotes_table();
		list( $where, $params ) = self::build_filters( $args );

		$sql = "SELECT COUNT(*) FROM {$table} WHERE " . implode( ' AND ', $where );
		if ( $params ) {
			$sql = $wpdb->prepare( $sql, $params ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql est une chaîne littérale construite juste au-dessus (interpolation du nom de table uniquement, jamais de donnée utilisateur).
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- $table est un nom de table interne ; $sql est déjà préparé via $wpdb->prepare() ci-dessus quand il y a des paramètres, et ne contient aucune donnée utilisateur sinon.
		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Construit la clause WHERE et les paramètres communs à all() et count().
	 *
	 * @param array $args Filtres optionnels (statut, search, date_from, date_to).
	 *
	 * @return array{0: string[], 1: array} [conditions WHERE, paramètres pour $wpdb->prepare()]
	 */
	private static function build_filters( array $args ): array {
		global $wpdb;

		$where  = array( '1=1' );
		$params = array();

		if ( ! empty( $args['statut'] ) ) {
			$where[]  = 'statut = %s';
			$params[] = $args['statut'];
		}
		if ( ! empty( $args['search'] ) ) {
			$like    = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where[] = '(nom LIKE %s OR telephone LIKE %s OR email LIKE %s OR ville_depart LIKE %s OR ville_arrivee LIKE %s)';
			array_push( $params, $like, $like, $like, $like, $like );
		}
		if ( ! empty( $args['date_from'] ) ) {
			$where[]  = 'created_at >= %s';
			$params[] = $args['date_from'];
		}
		if ( ! empty( $args['date_to'] ) ) {
			$where[]  = 'created_at <= %s';
			$params[] = $args['date_to'];
		}

		return array( $where, $params );
	}

	/**
	 * Insère ou met à jour un devis avec un id imposé (import de données
	 * existantes : l'UUID Supabase d'origine est conservé tel quel, voir
	 * Importer::import_quotes()). Idempotent : relancer avec le même id met
	 * juste à jour la ligne, sans créer de doublon.
	 *
	 * @param array $data Données complètes du devis, y compris 'id'.
	 */
	public function import_upsert( array $data ): void {
		global $wpdb;
		$table = Schema::quotes_table();

		if ( $this->find( $data['id'] ) ) {
			$wpdb->update( $table, $data, array( 'id' => $data['id'] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- table personnalisée du plugin, $wpdb->update() échappe déjà les valeurs.
		} else {
			$wpdb->insert( $table, $data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- table personnalisée du plugin, $wpdb->insert() échappe déjà les valeurs.
		}
	}

	/**
	 * Crée un devis.
	 *
	 * @param array $data Colonnes du devis (voir Schema::install() pour la liste complète).
	 *
	 * @return string UUID du devis créé.
	 */
	public function create( array $data ): string {
		global $wpdb;
		$table = Schema::quotes_table();

		$id = wp_generate_uuid4();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- table personnalisée du plugin, $wpdb->insert() échappe déjà les valeurs.
		$wpdb->insert(
			$table,
			array_merge(
				array(
					'id'             => $id,
					'statut'         => 'en_attente',
					'invoice_number' => \SierraLogistics\Invoice_Numbering::next_proforma(),
					'created_at'     => gmdate( 'Y-m-d H:i:s' ),
				),
				$data
			)
		);

		return $id;
	}

	/**
	 * Met à jour un devis.
	 *
	 * @param string $id   UUID du devis.
	 * @param array  $data Colonnes à mettre à jour.
	 */
	public function update( string $id, array $data ): bool {
		global $wpdb;
		$table = Schema::quotes_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- table personnalisée du plugin, $wpdb->update() échappe déjà les valeurs.
		return false !== $wpdb->update( $table, $data, array( 'id' => $id ) );
	}

	/**
	 * Supprime un devis.
	 *
	 * @param string $id UUID du devis.
	 */
	public function delete( string $id ): bool {
		global $wpdb;
		$table = Schema::quotes_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- table personnalisée du plugin, $wpdb->delete() échappe déjà les valeurs.
		return false !== $wpdb->delete( $table, array( 'id' => $id ), array( '%s' ) );
	}

	/**
	 * Passe un devis "en_attente" à "commandé", seulement si son statut
	 * actuel est bien "en_attente" (évite de valider deux fois la même
	 * commande en cas de double-clic ou de requêtes concurrentes).
	 *
	 * @param string $id UUID du devis.
	 */
	public function mark_as_ordered( string $id ): bool {
		global $wpdb;
		$table = Schema::quotes_table();
		$sql   = "UPDATE {$table} SET statut = 'commandé' WHERE id = %s AND statut = 'en_attente'";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- {$table} est un nom de table interne, jamais une donnée utilisateur ; $sql est une chaîne littérale assignée juste au-dessus.
		return (bool) $wpdb->query( $wpdb->prepare( $sql, $id ) );
	}
}
