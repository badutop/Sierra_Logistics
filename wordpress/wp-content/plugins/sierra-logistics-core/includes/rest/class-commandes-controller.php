<?php
/**
 * Route REST publique de lecture des commandes.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Rest;

use SierraLogistics\Repositories\Commandes_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GET /sierra/v1/commandes/{proforma_id} : seule route publique sur les
 * commandes, lecture seule, nécessaire à l'affichage de la facture
 * définitive (véhicule/chauffeur assignés) sur le front. Parité avec
 * GET /api/commandes?proformaId= actuel. La validation d'une commande
 * (assignation du véhicule) n'a pas d'équivalent public : c'est une action
 * de gestion, exclusivement dans wp-admin (voir migration/AUDIT.md §10
 * point 4).
 */
class Commandes_Controller {

	const UUID_PATTERN = '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}';

	/**
	 * Enregistre la route de lecture des commandes sur sierra/v1.
	 */
	public static function register_routes(): void {
		register_rest_route(
			'sierra/v1',
			'/commandes/(?P<proforma_id>' . self::UUID_PATTERN . ')',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( __CLASS__, 'get_by_proforma' ),
			)
		);
	}

	/**
	 * Retourne la commande associée à un devis.
	 *
	 * @param \WP_REST_Request $request Requête REST (paramètre proforma_id).
	 */
	public static function get_by_proforma( \WP_REST_Request $request ) {
		$repository = new Commandes_Repository();
		$commande   = $repository->find_by_proforma( $request->get_param( 'proforma_id' ) );

		if ( ! $commande ) {
			return new \WP_Error( 'sierra_not_found', __( 'Aucune commande pour ce devis.', 'sierra-logistics' ), array( 'status' => 404 ) );
		}

		return new \WP_REST_Response( $commande );
	}
}
