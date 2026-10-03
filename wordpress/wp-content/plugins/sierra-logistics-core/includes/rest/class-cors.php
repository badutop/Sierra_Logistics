<?php
/**
 * CORS de l'API REST publique.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Rest;

use SierraLogistics\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CORS pour le namespace sierra/v1 uniquement : remplace le comportement par
 * défaut de l'API REST WordPress (qui n'envoie des en-têtes CORS que pour les
 * origines déjà "autorisées" par WordPress lui-même) par une liste explicite
 * configurée dans les réglages du plugin, jamais codée en dur (voir
 * Settings::get('cors_allowed_origins')).
 */
class Cors {

	const NAMESPACE_PREFIX = 'sierra/v1';

	/**
	 * Remplace le gestionnaire CORS par défaut de WordPress pour sierra/v1.
	 */
	public static function register(): void {
		add_action(
			'rest_api_init',
			function () {
				remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
				add_filter( 'rest_pre_serve_request', array( __CLASS__, 'send_headers' ), 10, 4 );
			},
			15
		);
	}

	/**
	 * Envoie les en-têtes CORS pour les routes sierra/v1 si l'origine est autorisée.
	 *
	 * @param bool             $served  Valeur d'origine du filtre, retournée inchangée.
	 * @param mixed            $result  Non utilisé.
	 * @param \WP_REST_Request $request Requête REST en cours.
	 * @param \WP_REST_Server  $server  Non utilisé, imposé par la signature du filtre rest_pre_serve_request.
	 */
	public static function send_headers( $served, $result, $request, $server ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- $server est imposé par la signature du filtre WordPress.
		$route = $request->get_route();

		if ( 0 !== strpos( ltrim( $route, '/' ), self::NAMESPACE_PREFIX ) ) {
			return $served;
		}

		$origin = get_http_origin();

		if ( $origin && self::is_allowed_origin( $origin ) ) {
			header( 'Access-Control-Allow-Origin: ' . esc_url_raw( $origin ) );
			header( 'Access-Control-Allow-Methods: GET, POST, OPTIONS' );
			header( 'Access-Control-Allow-Headers: Content-Type' );
			header( 'Vary: Origin' );
		}

		return $served;
	}

	/**
	 * Vérifie si une origine HTTP est autorisée pour l'API publique.
	 *
	 * @param string $origin Origine envoyée par le navigateur (en-tête Origin).
	 */
	public static function is_allowed_origin( string $origin ): bool {
		$allowed = (array) Settings::get( 'cors_allowed_origins', array() );

		// Développement local : toujours autorisé, quel que soit le réglage,
		// pour ne pas avoir à reconfigurer les réglages à chaque session de
		// dev. N'a aucun effet en production (l'origine ne matchera jamais).
		$allowed[] = 'http://localhost:3000';
		$allowed[] = 'http://127.0.0.1:3000';

		return in_array( untrailingslashit( $origin ), array_map( 'untrailingslashit', $allowed ), true );
	}
}
