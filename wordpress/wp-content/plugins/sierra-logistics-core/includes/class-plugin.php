<?php
/**
 * Bootstrap principal du plugin.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Point d'entrée après le chargement de tous les plugins. Les hooks pour
 * l'API REST, le back-office et les rôles sont branchés ici au fur et à
 * mesure qu'ils sont implémentés.
 */
class Plugin {

	/**
	 * Charge les traductions et initialise les sous-systèmes du plugin.
	 */
	public static function init(): void {
		load_plugin_textdomain(
			'sierra-logistics',
			false,
			dirname( plugin_basename( SIERRA_LOGISTICS_PLUGIN_FILE ) ) . '/languages'
		);

		Schema::maybe_upgrade();
		Capabilities::maybe_upgrade();

		Rest\Bootstrap::init();
		Admin\Bootstrap::init();
	}
}
