<?php
/**
 * Réglages du plugin.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Point d'accès unique aux réglages du plugin (option unique en base,
 * sérialisée). Centralise tout ce qui doit rester configurable depuis
 * wp-admin plutôt que codé en dur : origines CORS, expéditeur e-mail,
 * coordonnées affichées sur les PDF, préfixes de numérotation, limite de
 * fréquence de l'API publique. Voir migration/DEPLOIEMENT.md - "Principe
 * directeur : zéro URL en dur".
 */
class Settings {

	const OPTION_KEY = 'sierra_logistics_settings';

	/**
	 * Valeurs par défaut de tous les réglages.
	 */
	public static function defaults(): array {
		return array(
			'zones_tarifaires'          => Pricing::default_zones(),
			'coefficients_camion'       => Pricing::default_coefficients(),
			'tva_rate'                  => Pricing::default_tva_rate(),
			'cors_allowed_origins'      => array(),
			'email_from_name'           => 'Sierra Logistics',
			'email_from_address'        => '',
			'notification_email'        => get_option( 'admin_email' ),
			'company_name'              => 'Sierra Logistics',
			'company_address'           => '',
			'company_phone'             => '',
			'company_ninea'             => '',
			'invoice_prefix_proforma'   => 'PRO',
			'invoice_prefix_definitive' => 'FAC',
			'rate_limit_per_hour'       => 10,
		);
	}

	/**
	 * Réglages effectifs : valeurs enregistrées fusionnées avec les valeurs
	 * par défaut.
	 */
	public static function all(): array {
		$stored = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		return array_merge( self::defaults(), $stored );
	}

	/**
	 * Lit un réglage par sa clé.
	 *
	 * @param string $key          Clé du réglage.
	 * @param mixed  $fallback_value Valeur retournée si la clé est absente.
	 */
	public static function get( string $key, $fallback_value = null ) {
		$all = self::all();
		return $all[ $key ] ?? $fallback_value;
	}

	/**
	 * Met à jour un sous-ensemble des réglages, en conservant le reste.
	 *
	 * @param array $partial Réglages à mettre à jour.
	 */
	public static function update( array $partial ): void {
		update_option( self::OPTION_KEY, array_merge( self::all(), $partial ) );
	}
}
