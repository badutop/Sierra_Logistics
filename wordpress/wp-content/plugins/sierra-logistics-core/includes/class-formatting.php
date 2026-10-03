<?php
/**
 * Fonctions d'affichage communes au back-office et aux factures.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formatage des montants et des dates, à l'identique de formatNumber() dans
 * src/lib/pricing.js et de l'affichage en heure de Dakar demandé pour les
 * dates (stockage en UTC, affichage Africa/Dakar).
 */
class Formatting {

	/**
	 * Formate un montant en FCFA, sans décimales, séparateur de milliers.
	 *
	 * @param float|int|null $amount Montant à formater.
	 */
	public static function fcfa( $amount ): string {
		return number_format( (float) $amount, 0, ',', ' ' ) . ' FCFA';
	}

	/**
	 * Convertit une date/heure UTC stockée en base vers l'heure de Dakar pour
	 * l'affichage.
	 *
	 * @param string|null $utc_datetime Date/heure au format MySQL (Y-m-d H:i:s), en UTC.
	 * @param string      $format       Format de sortie (voir date()).
	 */
	public static function to_dakar_time( ?string $utc_datetime, string $format = 'd/m/Y H:i' ): string {
		if ( ! $utc_datetime ) {
			return '-';
		}

		try {
			$date = new \DateTime( $utc_datetime, new \DateTimeZone( 'UTC' ) );
			$date->setTimezone( new \DateTimeZone( 'Africa/Dakar' ) );
			return $date->format( $format );
		} catch ( \Exception $e ) {
			return '-';
		}
	}
}
