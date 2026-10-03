<?php
/**
 * Routes REST publiques de référence (villes, types de camion, zones).
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Rest;

use SierraLogistics\Data\Villes_Senegal;
use SierraLogistics\Pricing;
use SierraLogistics\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Routes GET publiques en lecture seule, sans donnée personnelle : les
 * listes dont le formulaire de devis a besoin pour ses menus déroulants.
 */
class Reference_Controller {

	/**
	 * Enregistre les routes de référence sur sierra/v1.
	 */
	public static function register_routes(): void {
		register_rest_route(
			'sierra/v1',
			'/reference/villes',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( __CLASS__, 'get_villes' ),
			)
		);

		register_rest_route(
			'sierra/v1',
			'/reference/types-camion',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( __CLASS__, 'get_types_camion' ),
			)
		);

		register_rest_route(
			'sierra/v1',
			'/reference/zones-tarifaires',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( __CLASS__, 'get_zones' ),
			)
		);
	}

	/**
	 * Liste des villes disponibles pour les menus déroulants du devis.
	 */
	public static function get_villes(): \WP_REST_Response {
		$villes = array_keys( Villes_Senegal::all() );
		sort( $villes, SORT_LOCALE_STRING );
		return new \WP_REST_Response( $villes );
	}

	/**
	 * Liste des types de camion disponibles.
	 */
	public static function get_types_camion(): \WP_REST_Response {
		$settings = Settings::get( 'coefficients_camion', Pricing::default_coefficients() );
		return new \WP_REST_Response( array_keys( $settings ) );
	}

	/**
	 * Expose les libellés/bornes des zones, pas pour un calcul côté client
	 * (le calcul est toujours refait côté serveur, voir Quotes_Controller),
	 * seulement pour un affichage informatif éventuel sur le front.
	 */
	public static function get_zones(): \WP_REST_Response {
		$zones = Settings::get( 'zones_tarifaires', Pricing::default_zones() );

		return new \WP_REST_Response(
			array_map(
				function ( $zone ) {
					return array(
						'libelle' => $zone['libelle'],
						'min'     => $zone['min'],
						'max'     => PHP_INT_MAX === $zone['max'] ? null : $zone['max'],
					);
				},
				$zones
			)
		);
	}
}
