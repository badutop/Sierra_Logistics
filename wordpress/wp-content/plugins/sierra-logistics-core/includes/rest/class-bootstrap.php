<?php
/**
 * Enregistrement de l'API REST publique du plugin.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Rest;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Branche le CORS et les contrôleurs REST du namespace sierra/v1.
 */
class Bootstrap {

	/**
	 * Initialise le CORS et enregistre les routes REST publiques.
	 */
	public static function init(): void {
		Cors::register();

		add_action(
			'rest_api_init',
			function () {
				Quotes_Controller::register_routes();
				Commandes_Controller::register_routes();
				Vehicles_Controller::register_routes();
				Reference_Controller::register_routes();
			}
		);
	}
}
