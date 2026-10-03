<?php
/**
 * Validation d'un devis en commande (affectation d'un véhicule).
 *
 * @package SierraLogistics
 */

namespace SierraLogistics;

use SierraLogistics\Repositories\Commandes_Repository;
use SierraLogistics\Repositories\Quote_History_Repository;
use SierraLogistics\Repositories\Quotes_Repository;
use SierraLogistics\Repositories\Vehicles_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logique de validation d'une commande, partagée par le back-office (seul
 * point d'entrée actuel : cette action est une action de gestion, pas une
 * route publique, voir migration/AUDIT.md §10 point 4). Port de la logique
 * de src/app/api/commandes/valider/route.js.
 */
class Commande_Validator {

	/**
	 * Valide un devis en commande : réserve un véhicule (de préférence du
	 * type demandé, sinon n'importe quel véhicule disponible, sauf si un
	 * véhicule précis est imposé), crée la commande, passe le devis à
	 * "commandé".
	 *
	 * @param string      $quote_id           Devis à valider.
	 * @param string|null $preferred_vehicle_id Véhicule choisi manuellement dans le back-office, prioritaire s'il est disponible.
	 *
	 * @return array|\WP_Error La commande créée, ou une erreur.
	 */
	public static function validate( string $quote_id, ?string $preferred_vehicle_id = null ) {
		$quotes_repository = new Quotes_Repository();
		$quote             = $quotes_repository->find( $quote_id );

		if ( ! $quote ) {
			return new \WP_Error( 'sierra_not_found', __( 'Devis introuvable.', 'sierra-logistics' ) );
		}

		if ( 'en_attente' !== $quote['statut'] ) {
			return new \WP_Error( 'sierra_already_validated', __( 'Ce devis a déjà été validé.', 'sierra-logistics' ) );
		}

		$vehicles_repository = new Vehicles_Repository();
		$available           = $vehicles_repository->all( array( 'status' => 'disponible' ) );

		if ( empty( $available ) ) {
			return new \WP_Error( 'sierra_no_vehicle', __( 'Aucun camion disponible actuellement.', 'sierra-logistics' ) );
		}

		$candidates = self::order_candidates( $available, $quote['type_vehicle'], $preferred_vehicle_id );

		$vehicle = $vehicles_repository->claim_available( $candidates );
		if ( ! $vehicle ) {
			return new \WP_Error( 'sierra_no_vehicle', __( 'Aucun camion disponible actuellement.', 'sierra-logistics' ) );
		}

		$commandes_repository = new Commandes_Repository();
		$commande_id          = $commandes_repository->create(
			array(
				'proforma_id'            => $quote_id,
				'vehicle_id'             => $vehicle['id'],
				'camion_immatriculation' => $vehicle['license_plate'],
				'chauffeur'              => $vehicle['name'],
				'telephone_chauffeur'    => $vehicle['contact_phone'],
			),
			$vehicles_repository
		);

		if ( is_wp_error( $commande_id ) ) {
			return $commande_id;
		}

		$quotes_repository->mark_as_ordered( $quote_id );

		$history = new Quote_History_Repository();
		$history->log(
			$quote_id,
			sprintf(
				/* translators: 1: immatriculation du camion, 2: nom du chauffeur */
				__( 'Commande validée : camion %1$s, chauffeur %2$s.', 'sierra-logistics' ),
				$vehicle['license_plate'],
				$vehicle['name']
			),
			get_current_user_id() ? get_current_user_id() : null
		);

		return $commandes_repository->find_by_proforma( $quote_id );
	}

	/**
	 * Ordonne les véhicules candidats : le véhicule imposé en premier s'il
	 * est fourni, puis ceux du type demandé par le devis, puis le reste.
	 *
	 * @param array[]     $available     Véhicules disponibles (status = disponible).
	 * @param string|null $type_vehicle  Type de camion demandé par le devis.
	 * @param string|null $preferred_id  Véhicule choisi manuellement, s'il y en a un.
	 *
	 * @return string[] Identifiants des véhicules, dans l'ordre de priorité.
	 */
	private static function order_candidates( array $available, ?string $type_vehicle, ?string $preferred_id ): array {
		$ids = array_column( $available, 'id' );

		if ( $preferred_id && in_array( $preferred_id, $ids, true ) ) {
			return array( $preferred_id );
		}

		$matching = array();
		$rest     = array();

		foreach ( $available as $vehicle ) {
			if ( $type_vehicle && $vehicle['model'] === $type_vehicle ) {
				$matching[] = $vehicle['id'];
			} else {
				$rest[] = $vehicle['id'];
			}
		}

		return array_merge( $matching, $rest );
	}
}
