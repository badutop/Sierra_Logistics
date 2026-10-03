<?php
/**
 * Définition et création des tables personnalisées du plugin.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Définit et crée les tables wp_sierra_* via dbDelta().
 *
 * Les colonnes `status`/`statut` sont en VARCHAR, pas en ENUM, et aucune
 * contrainte FOREIGN KEY n'est déclarée ici : dbDelta() parse le SQL avec des
 * règles strictes et peu documentées (un champ par ligne, deux espaces avant
 * PRIMARY KEY/KEY) et ne gère ni les CHECK ni les FOREIGN KEY de façon fiable
 * d'une version à l'autre. La validation des valeurs autorisées et
 * l'intégrité référentielle sont donc imposées en PHP dans les classes
 * Repositories\*. Voir migration/sierra_schema_mysql.sql pour un schéma de
 * référence avec ENUM/FOREIGN KEY, utile pour un import manuel hors WordPress.
 */
class Schema {

	const OPTION_DB_VERSION = 'sierra_logistics_db_version';

	/**
	 * Nom complet (avec préfixe) de la table des véhicules.
	 */
	public static function vehicles_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'sierra_vehicles';
	}

	/**
	 * Nom complet (avec préfixe) de la table des devis.
	 */
	public static function quotes_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'sierra_quotes';
	}

	/**
	 * Nom complet (avec préfixe) de la table des commandes.
	 */
	public static function commandes_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'sierra_commandes';
	}

	/**
	 * Nom complet (avec préfixe) de la table d'historique des devis.
	 */
	public static function quote_history_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'sierra_quote_history';
	}

	/**
	 * Crée ou met à jour les tables du plugin via dbDelta().
	 */
	public static function install(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$vehicles  = self::vehicles_table();
		$quotes    = self::quotes_table();
		$commandes = self::commandes_table();
		$history   = self::quote_history_table();

		$sql_vehicles = "CREATE TABLE {$vehicles} (
			id char(36) NOT NULL,
			name varchar(255) NOT NULL,
			model varchar(100) NOT NULL,
			license_plate varchar(50) NOT NULL,
			fuel_type varchar(50) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'disponible',
			contact_phone varchar(50) DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY status (status),
			KEY model (model)
		) {$charset_collate};";

		$sql_quotes = "CREATE TABLE {$quotes} (
			id char(36) NOT NULL,
			nom varchar(255) DEFAULT NULL,
			email varchar(255) DEFAULT NULL,
			telephone varchar(50) DEFAULT NULL,
			ville_depart varchar(100) DEFAULT NULL,
			ville_arrivee varchar(100) DEFAULT NULL,
			type_marchandise varchar(100) DEFAULT NULL,
			poids decimal(12,2) DEFAULT NULL,
			type_vehicle varchar(100) DEFAULT NULL,
			date_expedition date DEFAULT NULL,
			infos_additionnelles text,
			distance decimal(10,2) DEFAULT NULL,
			zone varchar(100) DEFAULT NULL,
			tarif_zone decimal(12,2) DEFAULT NULL,
			coefficient_camion decimal(5,2) DEFAULT NULL,
			montant_transport decimal(14,2) DEFAULT NULL,
			majoration decimal(14,2) DEFAULT NULL,
			sous_total decimal(14,2) DEFAULT NULL,
			tva decimal(14,2) DEFAULT NULL,
			total decimal(14,2) DEFAULT NULL,
			statut varchar(20) NOT NULL DEFAULT 'en_attente',
			invoice_number varchar(30) DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY invoice_number (invoice_number),
			KEY telephone (telephone),
			KEY statut (statut)
		) {$charset_collate};";

		$sql_commandes = "CREATE TABLE {$commandes} (
			id char(36) NOT NULL,
			proforma_id char(36) NOT NULL,
			vehicle_id char(36) NOT NULL,
			camion_immatriculation varchar(50) NOT NULL,
			chauffeur varchar(255) NOT NULL,
			telephone_chauffeur varchar(50) DEFAULT NULL,
			invoice_number varchar(30) DEFAULT NULL,
			date_validation datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY proforma_id (proforma_id),
			UNIQUE KEY invoice_number (invoice_number),
			KEY vehicle_id (vehicle_id)
		) {$charset_collate};";

		$sql_history = "CREATE TABLE {$history} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			quote_id char(36) NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			user_id bigint(20) unsigned DEFAULT NULL,
			message text NOT NULL,
			PRIMARY KEY  (id),
			KEY quote_id (quote_id)
		) {$charset_collate};";

		dbDelta( $sql_vehicles );
		dbDelta( $sql_quotes );
		dbDelta( $sql_commandes );
		dbDelta( $sql_history );

		update_option( self::OPTION_DB_VERSION, SIERRA_LOGISTICS_DB_VERSION );
	}

	/**
	 * Rejoue install() si le plugin a été mis à jour et que le schéma a
	 * changé, sans attendre une désactivation/réactivation manuelle.
	 */
	public static function maybe_upgrade(): void {
		if ( get_option( self::OPTION_DB_VERSION ) !== SIERRA_LOGISTICS_DB_VERSION ) {
			self::install();
		}
	}
}
