<?php
/**
 * Activation du plugin.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tâches exécutées à l'activation du plugin.
 */
class Activator {

	/**
	 * Crée les tables et les options par défaut.
	 */
	public static function activate(): void {
		Schema::install();

		if ( ! get_option( 'sierra_logistics_keep_data_on_uninstall' ) ) {
			add_option( 'sierra_logistics_keep_data_on_uninstall', true );
		}

		Capabilities::register_roles();

		flush_rewrite_rules();
	}
}
