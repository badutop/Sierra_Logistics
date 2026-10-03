<?php
/**
 * Limitation de fréquence par IP pour l'API publique.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Rest;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Limitation de fréquence par IP via les transients (pas de table dédiée :
 * ce compteur est volontairement éphémère et best-effort, adapté à un
 * hébergement mutualisé sans cache objet persistant).
 */
class Rate_Limiter {

	/**
	 * Vérifie et comptabilise une tentative pour une IP et une action données.
	 *
	 * @param string $action       Nom de l'action limitée (ex. "create_quote").
	 * @param string $ip           Adresse IP du client.
	 * @param int    $max_per_hour Nombre maximum de tentatives par heure.
	 *
	 * @return bool true si la requête est autorisée (et comptée), false si la
	 *              limite est dépassée pour cette IP sur la fenêtre en cours.
	 */
	public static function allow( string $action, string $ip, int $max_per_hour ): bool {
		$key   = 'sierra_rl_' . $action . '_' . md5( $ip );
		$count = (int) get_transient( $key );

		if ( $count >= $max_per_hour ) {
			return false;
		}

		if ( 0 === $count ) {
			set_transient( $key, 1, HOUR_IN_SECONDS );
		} else {
			set_transient( $key, $count + 1, HOUR_IN_SECONDS );
		}

		return true;
	}

	/**
	 * Adresse IP du client. N'utilise REMOTE_ADDR que (pas d'en-tête
	 * X-Forwarded-For, trivialement falsifiable par le client lui-même tant
	 * qu'aucun proxy de confiance n'est explicitement configuré devant WP).
	 */
	public static function client_ip(): string {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
	}
}
