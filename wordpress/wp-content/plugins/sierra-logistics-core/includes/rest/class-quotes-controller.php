<?php
/**
 * Routes REST publiques des devis.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Rest;

use SierraLogistics\Data\Villes_Senegal;
use SierraLogistics\Pricing;
use SierraLogistics\Settings;
use SierraLogistics\Repositories\Quotes_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * POST /sierra/v1/quotes : seule route publique qui écrit des données. Le
 * prix est toujours recalculé ici (voir Pricing::calculer()) : aucun montant
 * envoyé par le client n'est jamais utilisé, contrairement au comportement
 * actuel du front (src/app/(site)/devis/devis-form.jsx), qui calcule le
 * devis dans le navigateur et écrit directement les montants en base (voir
 * migration/AUDIT.md §3).
 *
 * GET /sierra/v1/quotes/{id} et /quotes/lookup : lecture seule, nécessaires
 * à l'affichage public de la facture proforma et à la recherche "retrouver
 * mon devis par téléphone" (src/app/(site)/commander). Le filtrage par
 * téléphone est fait ici, côté serveur - contrairement à l'ancienne policy
 * RLS Supabase qui exposait toute la table quotes à la clé anon.
 */
class Quotes_Controller {

	const UUID_PATTERN = '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}';

	/**
	 * Enregistre les routes des devis sur sierra/v1.
	 */
	public static function register_routes(): void {
		register_rest_route(
			'sierra/v1',
			'/quotes',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( __CLASS__, 'create' ),
			)
		);

		register_rest_route(
			'sierra/v1',
			'/quotes/lookup',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( __CLASS__, 'lookup_by_telephone' ),
				'args'                => array(
					'telephone' => array( 'required' => true ),
				),
			)
		);

		register_rest_route(
			'sierra/v1',
			'/quotes/(?P<id>' . self::UUID_PATTERN . ')',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( __CLASS__, 'get_one' ),
			)
		);
	}

	/**
	 * Crée un devis : honeypot, rate-limit, validation, calcul serveur, notifications.
	 *
	 * @param \WP_REST_Request $request Requête REST (corps JSON).
	 */
	public static function create( \WP_REST_Request $request ) {
		$ip = Rate_Limiter::client_ip();

		if ( ! Rate_Limiter::allow( 'create_quote', $ip, (int) Settings::get( 'rate_limit_per_hour', 10 ) ) ) {
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

		// Honeypot : un champ "website" caché côté front, jamais rempli par
		// un visiteur humain. Rejet générique, sans préciser la raison.
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

		try {
			$pricing = new Pricing();
			$calcul  = $pricing->calculer( $data['ville_depart'], $data['ville_arrivee'], $data['type_vehicle'] );
		} catch ( \InvalidArgumentException $e ) {
			return new \WP_Error(
				'sierra_validation_failed',
				__( 'Certains champs sont invalides.', 'sierra-logistics' ),
				array(
					'status' => 422,
					'errors' => array( 'ville_depart' => $e->getMessage() ),
				)
			);
		}

		$repository = new Quotes_Repository();
		$id         = $repository->create(
			array(
				'nom'                  => $data['nom'],
				'email'                => $data['email'],
				'telephone'            => $data['telephone'],
				'ville_depart'         => $data['ville_depart'],
				'ville_arrivee'        => $data['ville_arrivee'],
				'type_marchandise'     => $data['type_marchandise'],
				'poids'                => $data['poids'],
				'type_vehicle'         => $data['type_vehicle'],
				'date_expedition'      => $data['date_expedition'],
				'infos_additionnelles' => $data['infos_additionnelles'],
				'distance'             => $calcul['distance'],
				'zone'                 => $calcul['zone'],
				'tarif_zone'           => $calcul['tarifZone'],
				'coefficient_camion'   => $calcul['coefficientCamion'],
				'montant_transport'    => $calcul['montantTransport'],
				'majoration'           => $calcul['majoration'],
				'sous_total'           => $calcul['sousTotal'],
				'tva'                  => $calcul['tva'],
				'total'                => $calcul['total'],
			)
		);

		$quote = $repository->find( $id );
		self::send_notifications( $quote );

		return new \WP_REST_Response( $quote, 201 );
	}

	/**
	 * Retourne un devis par son id.
	 *
	 * @param \WP_REST_Request $request Requête REST (paramètre id).
	 */
	public static function get_one( \WP_REST_Request $request ) {
		$repository = new Quotes_Repository();
		$quote      = $repository->find( $request->get_param( 'id' ) );

		if ( ! $quote ) {
			return new \WP_Error( 'sierra_not_found', __( 'Devis introuvable.', 'sierra-logistics' ), array( 'status' => 404 ) );
		}

		return new \WP_REST_Response( $quote );
	}

	/**
	 * Retourne l'id du devis le plus récent pour un numéro de téléphone.
	 *
	 * @param \WP_REST_Request $request Requête REST (paramètre telephone).
	 */
	public static function lookup_by_telephone( \WP_REST_Request $request ) {
		$telephone = sanitize_text_field( (string) $request->get_param( 'telephone' ) );

		if ( '' === trim( $telephone ) ) {
			return new \WP_Error( 'sierra_validation_failed', __( 'Numéro de téléphone requis.', 'sierra-logistics' ), array( 'status' => 422 ) );
		}

		$repository = new Quotes_Repository();
		$matches    = $repository->find_by_telephone( $telephone, 1 );

		if ( empty( $matches ) ) {
			return new \WP_Error( 'sierra_not_found', __( 'Aucun devis trouvé pour ce numéro.', 'sierra-logistics' ), array( 'status' => 404 ) );
		}

		return new \WP_REST_Response( array( 'id' => $matches[0]['id'] ) );
	}

	/**
	 * Valide et nettoie les champs d'une demande de devis.
	 *
	 * @param array $params Champs bruts envoyés par le client.
	 *
	 * @return array{0: array, 1: array<string,string>} [données nettoyées, erreurs par champ]
	 */
	private static function validate( array $params ): array {
		$errors = array();
		$data   = array();

		$data['nom'] = isset( $params['nom'] ) ? sanitize_text_field( $params['nom'] ) : '';
		if ( '' === $data['nom'] ) {
			$errors['nom'] = __( 'Le nom est requis.', 'sierra-logistics' );
		}

		$data['email'] = isset( $params['email'] ) ? sanitize_email( $params['email'] ) : '';
		if ( '' === $data['email'] || ! is_email( $data['email'] ) ) {
			$errors['email'] = __( 'Adresse email invalide.', 'sierra-logistics' );
		}

		$data['telephone'] = isset( $params['telephone'] ) ? sanitize_text_field( $params['telephone'] ) : '';
		if ( '' === trim( $data['telephone'] ) ) {
			$errors['telephone'] = __( 'Le téléphone est requis.', 'sierra-logistics' );
		}

		$data['ville_depart'] = isset( $params['ville_depart'] ) ? sanitize_text_field( $params['ville_depart'] ) : '';
		if ( null === Villes_Senegal::distance_to_dakar( $data['ville_depart'] ) ) {
			$errors['ville_depart'] = __( 'Ville de départ invalide.', 'sierra-logistics' );
		}

		$data['ville_arrivee'] = isset( $params['ville_arrivee'] ) ? sanitize_text_field( $params['ville_arrivee'] ) : '';
		if ( null === Villes_Senegal::distance_to_dakar( $data['ville_arrivee'] ) ) {
			$errors['ville_arrivee'] = __( 'Ville d\'arrivée invalide.', 'sierra-logistics' );
		}

		$data['type_marchandise'] = isset( $params['type_marchandise'] ) ? sanitize_text_field( $params['type_marchandise'] ) : '';
		if ( '' === $data['type_marchandise'] ) {
			$errors['type_marchandise'] = __( 'Le type de marchandise est requis.', 'sierra-logistics' );
		}

		$data['poids'] = isset( $params['poids'] ) ? (float) $params['poids'] : 0;
		if ( $data['poids'] <= 0 ) {
			$errors['poids'] = __( 'Le poids doit être supérieur à zéro.', 'sierra-logistics' );
		}

		$data['type_vehicle'] = isset( $params['type_vehicle'] ) ? sanitize_text_field( $params['type_vehicle'] ) : '';
		$coefficients         = Settings::get( 'coefficients_camion', Pricing::default_coefficients() );
		if ( ! array_key_exists( $data['type_vehicle'], $coefficients ) ) {
			$errors['type_vehicle'] = __( 'Type de camion invalide.', 'sierra-logistics' );
		}

		$data['date_expedition'] = isset( $params['date_expedition'] ) ? sanitize_text_field( $params['date_expedition'] ) : '';
		$date                    = \DateTime::createFromFormat( 'Y-m-d', $data['date_expedition'] );
		if ( ! $date || $date->format( 'Y-m-d' ) !== $data['date_expedition'] ) {
			$errors['date_expedition'] = __( 'Date d\'expédition invalide (format attendu : AAAA-MM-JJ).', 'sierra-logistics' );
		}

		$data['infos_additionnelles'] = isset( $params['infos_additionnelles'] )
			? sanitize_textarea_field( $params['infos_additionnelles'] )
			: '';

		return array( $data, $errors );
	}

	/**
	 * E-mail de confirmation au client + notification à l'équipe. Nouveauté
	 * par rapport à l'existant (voir migration/AUDIT.md §4 : aucun e-mail
	 * n'était envoyé automatiquement à la soumission jusqu'ici).
	 *
	 * @param array $quote Devis venant d'être créé.
	 */
	private static function send_notifications( array $quote ): void {
		$invoice_url  = home_url( '/facture-proforma?id=' . $quote['id'] );
		$from_name    = Settings::get( 'email_from_name', 'Sierra Logistics' );
		$from_address = Settings::get( 'email_from_address' );
		$headers      = array();

		if ( $from_address ) {
			$headers[] = sprintf( 'From: %s <%s>', $from_name, $from_address );
		}

		if ( $quote['email'] ) {
			wp_mail(
				$quote['email'],
				__( 'Votre demande de devis - Sierra Logistics', 'sierra-logistics' ),
				sprintf(
					/* translators: 1: nom du client, 2: lien vers la facture proforma */
					__( "Bonjour %1\$s,\n\nVotre demande de devis a bien été reçue. Vous pouvez la consulter ici :\n%2\$s\n\nNotre équipe revient vers vous rapidement.\n\nSierra Logistics", 'sierra-logistics' ),
					$quote['nom'],
					$invoice_url
				),
				$headers
			);
		}

		$notification_email = Settings::get( 'notification_email' );
		if ( $notification_email ) {
			wp_mail(
				$notification_email,
				__( 'Nouvelle demande de devis', 'sierra-logistics' ),
				sprintf(
					/* translators: 1: nom du client, 2: lien vers la facture proforma */
					__( "Nouvelle demande de %1\$s :\n%2\$s", 'sierra-logistics' ),
					$quote['nom'],
					$invoice_url
				),
				$headers
			);
		}
	}
}
