<?php
/**
 * Bootstrap PHPUnit minimal, sans la suite de tests WordPress complète : les
 * classes couvertes ici (Pricing, Villes_Senegal) n'appellent que deux
 * fonctions WP (get_option(), remove_accents()), stubbées ci-dessous.
 * Suffisant pour les tests unitaires de calcul de devis ; les tests
 * d'intégration de l'API REST (étape "API REST publique") chargeront la
 * suite WP complète via WP_UnitTestCase.
 */

define( 'ABSPATH', __DIR__ . '/' );

$GLOBALS['sierra_test_options'] = array();

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $name, $default = false ) {
		return $GLOBALS['sierra_test_options'][ $name ] ?? $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( $name, $value ) {
		$GLOBALS['sierra_test_options'][ $name ] = $value;
		return true;
	}
}

if ( ! function_exists( 'remove_accents' ) ) {
	function remove_accents( $string ) {
		static $map = array(
			'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a',
			'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
			'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
			'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
			'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
			'ç' => 'c', 'ñ' => 'n',
		);
		return strtr( $string, $map );
	}
}

require_once dirname( __DIR__ ) . '/includes/data/class-villes-senegal.php';
require_once dirname( __DIR__ ) . '/includes/class-pricing.php';
