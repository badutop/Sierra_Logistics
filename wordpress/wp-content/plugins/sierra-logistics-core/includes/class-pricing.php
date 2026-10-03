<?php
/**
 * Calcul tarifaire des devis.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics;

use SierraLogistics\Data\Villes_Senegal;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Port exact de src/lib/pricing.js (calculerDistance, determinerZone,
 * calculerDevis). Les paramètres tarifaires ont des valeurs par défaut
 * identiques à l'existant mais sont lus depuis les réglages du plugin
 * (option 'sierra_logistics_settings') dès qu'ils y sont présents, pour
 * rester modifiables depuis l'admin sans toucher au code.
 *
 * Le calcul est TOUJOURS refait ici côté serveur : voir Rest\Quotes_Controller
 * (étape "API REST publique"), qui ignore tout montant envoyé par le client.
 */
class Pricing {

	/**
	 * Zones tarifaires par défaut (FCFA/km selon la tranche de distance).
	 *
	 * @return array<int, array{min:int, max:float, tarif:int, libelle:string}>
	 */
	public static function default_zones(): array {
		return array(
			array(
				'min'     => 1,
				'max'     => 100,
				'tarif'   => 500,
				'libelle' => 'Zone 1 (1-100 km)',
			),
			array(
				'min'     => 101,
				'max'     => 300,
				'tarif'   => 700,
				'libelle' => 'Zone 2 (101-300 km)',
			),
			array(
				'min'     => 301,
				'max'     => 500,
				'tarif'   => 800,
				'libelle' => 'Zone 3 (301-500 km)',
			),
			array(
				'min'     => 501,
				'max'     => 750,
				'tarif'   => 900,
				'libelle' => 'Zone 4 (501-750 km)',
			),
			array(
				'min'     => 751,
				'max'     => PHP_INT_MAX,
				'tarif'   => 1000,
				'libelle' => 'Zone 5 (751+ km)',
			),
		);
	}

	/**
	 * Coefficients multiplicateurs par type de camion.
	 *
	 * @return array<string, float>
	 */
	public static function default_coefficients(): array {
		return array(
			'Camion benne'        => 1.0,
			'Camion frigorifique' => 1.7,
			'Camion bâché'        => 1.2,
			'Camion citerne'      => 1.5,
			'Plateau'             => 1.3,
			'Fourgon'             => 1.1,
		);
	}

	/**
	 * Taux de TVA par défaut.
	 */
	public static function default_tva_rate(): float {
		return 0.18;
	}

	/**
	 * Zones tarifaires effectives (réglages admin ou valeurs par défaut).
	 *
	 * @return array<int, array{min:int, max:float, tarif:int, libelle:string}>
	 */
	private function zones(): array {
		$settings = get_option( 'sierra_logistics_settings', array() );
		return $settings['zones_tarifaires'] ?? self::default_zones();
	}

	/**
	 * Coefficients camion effectifs (réglages admin ou valeurs par défaut).
	 *
	 * @return array<string, float>
	 */
	private function coefficients(): array {
		$settings = get_option( 'sierra_logistics_settings', array() );
		return $settings['coefficients_camion'] ?? self::default_coefficients();
	}

	/**
	 * Taux de TVA effectif (réglages admin ou valeur par défaut).
	 */
	private function tva_rate(): float {
		$settings = get_option( 'sierra_logistics_settings', array() );
		return isset( $settings['tva_rate'] ) ? (float) $settings['tva_rate'] : self::default_tva_rate();
	}

	/**
	 * Distance entre deux villes, par différence de distance à Dakar
	 * (approximation, pas un itinéraire routier réel).
	 *
	 * @param string $ville_depart  Ville de départ.
	 * @param string $ville_arrivee Ville d'arrivée.
	 *
	 * @throws \InvalidArgumentException Si une des deux villes est inconnue.
	 */
	public function distance( string $ville_depart, string $ville_arrivee ): float {
		$depart  = Villes_Senegal::distance_to_dakar( $ville_depart );
		$arrivee = Villes_Senegal::distance_to_dakar( $ville_arrivee );

		if ( null === $depart ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- message d'exception interne, jamais rendu comme HTML.
			throw new \InvalidArgumentException( sprintf( 'Ville de départ "%s" non trouvée', $ville_depart ) );
		}
		if ( null === $arrivee ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- message d'exception interne, jamais rendu comme HTML.
			throw new \InvalidArgumentException( sprintf( 'Ville d\'arrivée "%s" non trouvée', $ville_arrivee ) );
		}

		return abs( $arrivee - $depart );
	}

	/**
	 * Zone tarifaire correspondant à une distance donnée.
	 *
	 * @param float $distance Distance en km.
	 *
	 * @return array{min:int, max:float, tarif:int, libelle:string}
	 */
	public function zone_for_distance( float $distance ): array {
		foreach ( $this->zones() as $zone ) {
			if ( $distance >= $zone['min'] && $distance <= $zone['max'] ) {
				return $zone;
			}
		}
		$zones = $this->zones();
		return end( $zones );
	}

	/**
	 * Calcule un devis complet (distance, zone, montants, TVA, total).
	 *
	 * @param string $ville_depart  Ville de départ.
	 * @param string $ville_arrivee Ville d'arrivée.
	 * @param string $type_camion   Type de camion demandé.
	 *
	 * @return array{distance:int, zone:string, tarifZone:int, coefficientCamion:float,
	 *               montantTransport:int, majoration:int, sousTotal:int, tva:int, total:int}
	 */
	public function calculer( string $ville_depart, string $ville_arrivee, string $type_camion ): array {
		$distance    = $this->distance( $ville_depart, $ville_arrivee );
		$zone        = $this->zone_for_distance( $distance );
		$coefficient = $this->coefficients()[ $type_camion ] ?? 1.0;

		$montant_transport = $zone['tarif'] * $distance;
		$majoration        = ( $coefficient - 1 ) * $zone['tarif'] * $distance;
		$sous_total        = $montant_transport + $majoration;
		$tva               = $sous_total * $this->tva_rate();
		$total             = $sous_total + $tva;

		return array(
			'distance'          => (int) round( $distance ),
			'zone'              => $zone['libelle'],
			'tarifZone'         => $zone['tarif'],
			'coefficientCamion' => $coefficient,
			'montantTransport'  => (int) round( $montant_transport ),
			'majoration'        => (int) round( $majoration ),
			'sousTotal'         => (int) round( $sous_total ),
			'tva'               => (int) round( $tva ),
			'total'             => (int) round( $total ),
		);
	}
}
