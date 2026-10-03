<?php
/**
 * Bootstrap du back-office.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Branche le menu, les pages et les actions de formulaire du back-office.
 */
class Bootstrap {

	/**
	 * Initialise le back-office (menu, pages, assets).
	 */
	public static function init(): void {
		if ( ! is_admin() ) {
			return;
		}

		Menu::init();
		Quotes_Page::register();
		Commandes_Page::register();
		Vehicles_Page::register();
		Settings_Page::register();

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Charge la feuille de style du back-office, uniquement sur les pages du plugin.
	 *
	 * @param string $hook_suffix Identifiant de la page d'administration courante.
	 */
	public static function enqueue_assets( string $hook_suffix ): void {
		if ( false === strpos( $hook_suffix, 'sierra' ) ) {
			return;
		}

		wp_enqueue_style(
			'sierra-logistics-admin',
			SIERRA_LOGISTICS_PLUGIN_URL . 'assets/admin.css',
			array(),
			SIERRA_LOGISTICS_VERSION
		);
	}
}
