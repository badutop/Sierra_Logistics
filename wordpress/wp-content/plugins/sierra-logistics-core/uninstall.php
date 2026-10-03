<?php
/**
 * Désinstallation du plugin.
 *
 * Déclenché uniquement par une suppression explicite du plugin depuis
 * wp-admin (jamais par une simple désactivation, voir class-deactivator.php).
 *
 * @package SierraLogistics
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Les rôles/capabilities sont des réglages fonctionnels du plugin, pas des
// données métier : on les retire toujours, indépendamment de l'option
// "conserver les données" ci-dessous (qui ne concerne que les tables).
require_once __DIR__ . '/includes/class-capabilities.php';
\SierraLogistics\Capabilities::remove_roles();
delete_option( \SierraLogistics\Capabilities::OPTION_ROLE_VERSION );

// Par défaut on conserve les données : qui désinstalle le plugin ne veut pas
// forcément perdre l'historique des devis/commandes/véhicules.
if ( get_option( 'sierra_logistics_keep_data_on_uninstall', true ) ) {
	return;
}

global $wpdb;

$tables = array(
	$wpdb->prefix . 'sierra_commandes',
	$wpdb->prefix . 'sierra_quotes',
	$wpdb->prefix . 'sierra_vehicles',
);

foreach ( $tables as $table ) {
	// Nom de table : jamais fourni par un utilisateur, non paramétrable via
	// $wpdb->prepare() (qui ne gère que les valeurs, pas les identifiants).
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
	$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" );
}

delete_option( 'sierra_logistics_db_version' );
delete_option( 'sierra_logistics_keep_data_on_uninstall' );
delete_option( 'sierra_logistics_settings' );
