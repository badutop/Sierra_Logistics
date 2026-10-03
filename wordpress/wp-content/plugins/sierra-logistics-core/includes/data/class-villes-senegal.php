<?php
/**
 * Référentiel des villes du Sénégal utilisées pour le calcul des devis.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Port de src/data/villes-senegal.json.
 *
 * Distance en km depuis Dakar pour chaque préfecture ; Pricing::distance()
 * calcule la distance entre deux villes par différence de ces valeurs
 * (approximation, pas un itinéraire routier réel - voir migration/AUDIT.md
 * §3 et §10 point 6).
 */
class Villes_Senegal {

	/**
	 * Liste des villes et leur distance à Dakar.
	 *
	 * @return array<string, int> Nom de ville => distance à Dakar (km).
	 */
	public static function all(): array {
		return array(
			'Dakar'              => 0,
			'Bakel'              => 650,
			'Bambey'             => 120,
			'Bignona'            => 150,
			'Birkelane'          => 230,
			'Bounkiling'         => 380,
			'Dagana'             => 200,
			'Diourbel'           => 140,
			'Fatick'             => 150,
			'Foundiougne'        => 130,
			'Goudiry'            => 500,
			'Goudomp'            => 350,
			'Guédiawaye'         => 10,
			'Guinguinéo'         => 160,
			'Kaffrine'           => 250,
			'Kanel'              => 500,
			'Kaolack'            => 190,
			'Kébémer'            => 120,
			'Kédougou'           => 700,
			'Keur Massar'        => 20,
			'Kolda'              => 420,
			'Koumpentoum'        => 450,
			'Koungheul'          => 220,
			'Linguère'           => 200,
			'Louga'              => 200,
			'Malem-Hodar'        => 240,
			'Matam'              => 550,
			'Mbacké'             => 190,
			'Mbour'              => 80,
			'Médina Yoro Foulah' => 480,
			'Nioro du Rip'       => 220,
			'Oussouye'           => 140,
			'Podor'              => 370,
			'Pikine'             => 15,
			'Ranérou'            => 520,
			'Rufisque'           => 25,
			'Saint-Louis'        => 260,
			'Salemata'           => 750,
			'Saraya'             => 720,
			'Sédhiou'            => 400,
			'Tambacounda'        => 460,
			'Tivaouane'          => 100,
			'Vélingara'          => 450,
			'Ziguinchor'         => 270,
		);
	}

	/**
	 * Normalise un nom de ville pour une comparaison insensible à la casse et
	 * aux accents, à l'identique de normaliser() dans src/lib/pricing.js.
	 *
	 * @param string $name Nom de ville à normaliser.
	 */
	public static function normalize( string $name ): string {
		$name           = mb_strtolower( trim( $name ), 'UTF-8' );
		$transliterated = remove_accents( $name );
		return trim( $transliterated );
	}

	/**
	 * Distance à Dakar pour un nom de ville donné, ou null si inconnue.
	 *
	 * @param string $name Nom de ville recherché.
	 */
	public static function distance_to_dakar( string $name ): ?int {
		$target = self::normalize( $name );
		foreach ( self::all() as $ville => $distance ) {
			if ( self::normalize( $ville ) === $target ) {
				return $distance;
			}
		}
		return null;
	}
}
