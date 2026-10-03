<?php
/**
 * Route REST publique d'inscription de véhicule.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Rest;

use SierraLogistics\Pricing;
use SierraLogistics\Settings;
use SierraLogistics\Repositories\Vehicles_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * POST /sierra/v1/vehicles : inscription d'un camion par un transporteur
 * partenaire, depuis le formulaire public /inscription-camion. Parité avec
 * l'INSERT direct dans `vehicles` par la clé anon Supabase (voir
 * migration/AUDIT.md §2.1) : même honeypot/rate-limit/validation que
 * POST /quotes, le statut "en_course" n'est jamais choisi par le
 * transporteur (seulement par Commande_Validator côté gestion).
 */
class Vehicles_Controller {

	/**
	 * Enregistre la route d'inscription sur sierra/v1.
	 */
	public static function register_routes(): void {
		register_rest_route(
			'sierra/v1',
			'/vehicles',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( __CLASS__, 'create' ),
			)
		);
	}

	/**
	 * Inscrit un véhicule.
	 *
	 * @param \WP_REST_Request $request Requête REST (corps JSON).
	 */
	public static function create( \WP_REST_Request $request ) {
		$ip = Rate_Limiter::client_ip();

		if ( ! Rate_Limiter::allow( 'create_vehicle', $ip, (int) Settings::get( 'rate_limit_per_hour', 10 ) ) ) {
			return new \WP_Error(
				'sierra_rate_limited',
				__( 'Trop de demandes depuis cette adresse. Merci de réessayer dans quelques minutes.', 'sierra-logistics' ),
				array( 'status' => 429 )
			);
		}

		$params = $request->get_json_params();
		if ( ! is_array( $params ) ) {
			$params = array();
		}

		if ( ! empty( $params['website'] ) ) {
			return new \WP_Error(
				'sierra_invalid_request',
				__( 'Requête invalide.', 'sierra-logistics' ),
				array( 'status' => 400 )
			);
		}

		list( $data, $errors ) = self::validate( $params );

		if ( ! empty( $errors ) ) {
			return new \WP_Error(
				'sierra_validation_failed',
				__( 'Certains champs sont invalides.', 'sierra-logistics' ),
				array(
					'status' => 422,
					'errors' => $errors,
				)
			);
		}

		$id = ( new Vehicles_Repository() )->create( $data );

		return new \WP_REST_Response( array( 'id' => $id ), 201 );
	}

	/**
	 * Valide et nettoie les champs d'une inscription de véhicule.
	 *
	 * @param array $params Champs bruts envoyés par le client.
	 *
	 * @return array{0: array, 1: array<string,string>} [données nettoyées, erreurs par champ]
	 */
	private static function validate( array $params ): array {
		$errors = array();
		$data   = array();

		$data['name'] = isset( $params['name'] ) ? sanitize_text_field( $params['name'] ) : '';
		if ( '' === $data['name'] ) {
			$errors['name'] = __( 'Le nom du propriétaire est requis.', 'sierra-logistics' );
		}

		$truck_types   = array_keys( Settings::get( 'coefficients_camion', Pricing::default_coefficients() ) );
		$data['model'] = isset( $params['model'] ) ? sanitize_text_field( $params['model'] ) : '';
		if ( ! in_array( $data['model'], $truck_types, true ) ) {
			$errors['model'] = __( 'Modèle de camion invalide.', 'sierra-logistics' );
		}

		$data['license_plate'] = isset( $params['license_plate'] ) ? sanitize_text_field( $params['license_plate'] ) : '';
		if ( '' === $data['license_plate'] ) {
			$errors['license_plate'] = __( "Le numéro d'immatriculation est requis.", 'sierra-logistics' );
		}

		$data['fuel_type'] = isset( $params['fuel_type'] ) ? sanitize_text_field( $params['fuel_type'] ) : '';
		if ( ! in_array( $data['fuel_type'], array( 'Diesel', 'Essence' ), true ) ) {
			$errors['fuel_type'] = __( 'Type de carburant invalide.', 'sierra-logistics' );
		}

		// "en_course" est un statut de gestion interne (réservation de
		// véhicule), jamais choisi par le transporteur lui-même.
		$data['status'] = isset( $params['status'] ) ? sanitize_text_field( $params['status'] ) : 'disponible';
		if ( ! in_array( $data['status'], array( 'disponible', 'maintenance' ), true ) ) {
			$errors['status'] = __( 'Statut invalide.', 'sierra-logistics' );
		}

		$data['contact_phone'] = isset( $params['contact_phone'] ) ? sanitize_text_field( $params['contact_phone'] ) : '';
		if ( '' === $data['contact_phone'] ) {
			$errors['contact_phone'] = __( 'Le téléphone du transporteur est requis.', 'sierra-logistics' );
		}

		return array( $data, $errors );
	}
}
