<?php
/**
 * Numérotation séquentielle des factures.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Génère des numéros de facture séquentiels, sans trou, par année et par
 * type de document (proforma / définitive). Nouveauté par rapport à
 * l'existant (voir migration/AUDIT.md §5 : l'ancien "N°" n'était qu'un
 * fragment d'UUID, ni séquentiel ni lisible).
 *
 * L'incrémentation utilise une mise à jour SQL directe sur wp_options
 * (`option_value = option_value + 1`), atomique au niveau de la ligne même
 * sous forte concurrence, sans nécessiter de table ni de verrou dédiés.
 */
class Invoice_Numbering {

	/**
	 * Génère et retourne le prochain numéro de facture proforma de l'année en cours.
	 */
	public static function next_proforma(): string {
		return self::next( Settings::get( 'invoice_prefix_proforma', 'PRO' ), 'proforma' );
	}

	/**
	 * Génère et retourne le prochain numéro de facture définitive de l'année en cours.
	 */
	public static function next_definitive(): string {
		return self::next( Settings::get( 'invoice_prefix_definitive', 'FAC' ), 'definitive' );
	}

	/**
	 * Génère un numéro au format "{PREFIXE}-{ANNÉE}-{SÉQUENCE sur 5 chiffres}".
	 *
	 * @param string $prefix Préfixe du numéro (depuis les réglages).
	 * @param string $series Série de numérotation (une par type de document et par année).
	 */
	private static function next( string $prefix, string $series ): string {
		$year     = gmdate( 'Y' );
		$sequence = self::next_sequence( $series . '_' . $year );

		return sprintf( '%s-%s-%05d', $prefix, $year, $sequence );
	}

	/**
	 * Incrémente et retourne un compteur atomique stocké dans wp_options.
	 *
	 * @param string $key Identifiant de la série (ex. "proforma_2026").
	 */
	private static function next_sequence( string $key ): int {
		global $wpdb;
		$option_name = 'sierra_logistics_invoice_seq_' . $key;

		if ( false === get_option( $option_name, false ) ) {
			add_option( $option_name, 0, '', 'no' );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- incrémentation atomique d'un compteur, l'API Options de WordPress n'offre pas d'équivalent (get/update n'est pas atomique sous forte concurrence).
		$wpdb->query(
			$wpdb->prepare( "UPDATE {$wpdb->options} SET option_value = option_value + 1 WHERE option_name = %s", $option_name )
		);

		wp_cache_delete( $option_name, 'options' );

		return (int) get_option( $option_name );
	}
}
