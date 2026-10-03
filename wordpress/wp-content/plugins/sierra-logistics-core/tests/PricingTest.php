<?php
use PHPUnit\Framework\TestCase;
use SierraLogistics\Pricing;

/**
 * Cas réels 1 et 2 tirés de la base Supabase actuelle (lecture seule, voir
 * migration/AUDIT.md §2.4) : les totaux doivent rester identiques au franc
 * près après le portage PHP de src/lib/pricing.js.
 */
final class PricingTest extends TestCase {

	public function test_devis_reel_dakar_bambey_camion_bache(): void {
		$pricing = new Pricing();
		$result  = $pricing->calculer( 'Dakar', 'Bambey', 'Camion bâché' );

		$this->assertSame( 120, $result['distance'] );
		$this->assertSame( 'Zone 2 (101-300 km)', $result['zone'] );
		$this->assertSame( 700, $result['tarifZone'] );
		$this->assertEqualsWithDelta( 1.2, $result['coefficientCamion'], 0.0001 );
		$this->assertSame( 84000, $result['montantTransport'] );
		$this->assertSame( 16800, $result['majoration'] );
		$this->assertSame( 100800, $result['sousTotal'] );
		$this->assertSame( 18144, $result['tva'] );
		$this->assertSame( 118944, $result['total'] );
	}

	public function test_devis_reel_dakar_bakel_camion_bache(): void {
		$pricing = new Pricing();
		$result  = $pricing->calculer( 'Dakar', 'Bakel', 'Camion bâché' );

		$this->assertSame( 650, $result['distance'] );
		$this->assertSame( 'Zone 4 (501-750 km)', $result['zone'] );
		$this->assertSame( 900, $result['tarifZone'] );
		$this->assertSame( 585000, $result['montantTransport'] );
		$this->assertSame( 117000, $result['majoration'] );
		$this->assertSame( 702000, $result['sousTotal'] );
		$this->assertSame( 126360, $result['tva'] );
		$this->assertSame( 828360, $result['total'] );
	}

	public function test_zone_1_courte_distance(): void {
		$pricing = new Pricing();
		// Pikine (15) -> Rufisque (25) = 10 km, Zone 1 (1-100 km), 500 FCFA/km.
		$result = $pricing->calculer( 'Pikine', 'Rufisque', 'Camion benne' );

		$this->assertSame( 10, $result['distance'] );
		$this->assertSame( 'Zone 1 (1-100 km)', $result['zone'] );
		$this->assertSame( 500, $result['tarifZone'] );
		$this->assertSame( 0, $result['majoration'], 'Coefficient 1.0 => pas de majoration' );
		$this->assertSame( 5000, $result['montantTransport'] );
		$this->assertSame( 900, $result['tva'] );
		$this->assertSame( 5900, $result['total'] );
	}

	public function test_zone_5_tres_longue_distance(): void {
		$pricing = new Pricing();
		// Dakar (0) -> Salemata (750) = 750 km, encore Zone 4 (borne max incluse).
		$result = $pricing->calculer( 'Dakar', 'Salemata', 'Camion benne' );
		$this->assertSame( 'Zone 4 (501-750 km)', $result['zone'] );

		// Dakar (0) -> Saraya (720) = 720 km, Zone 4 aussi.
		// Kédougou (700) -> Salemata (750) = 50 km, Zone 1.
		$result2 = $pricing->calculer( 'Kédougou', 'Salemata', 'Camion benne' );
		$this->assertSame( 'Zone 1 (1-100 km)', $result2['zone'] );
	}

	public function test_type_camion_inconnu_retombe_sur_coefficient_1(): void {
		$pricing = new Pricing();
		$result  = $pricing->calculer( 'Dakar', 'Bambey', 'Type Inexistant' );

		$this->assertEqualsWithDelta( 1.0, $result['coefficientCamion'], 0.0001 );
		$this->assertSame( 0, $result['majoration'] );
	}

	public function test_ville_depart_inconnue_leve_une_exception(): void {
		$pricing = new Pricing();
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Ville de départ "Paris" non trouvée' );
		$pricing->calculer( 'Paris', 'Dakar', 'Camion benne' );
	}

	public function test_ville_arrivee_inconnue_leve_une_exception(): void {
		$pricing = new Pricing();
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Ville d\'arrivée "Atlantide" non trouvée' );
		$pricing->calculer( 'Dakar', 'Atlantide', 'Camion benne' );
	}

	public function test_normalisation_insensible_a_la_casse_et_aux_accents(): void {
		$pricing = new Pricing();
		$a = $pricing->calculer( 'Dakar', 'Guédiawaye', 'Camion benne' );
		$b = $pricing->calculer( 'dakar', 'GUEDIAWAYE', 'Camion benne' );

		$this->assertSame( $a, $b );
		$this->assertSame( 10, $a['distance'] );
	}

	public function test_reglages_personnalises_remplacent_les_valeurs_par_defaut(): void {
		update_option(
			'sierra_logistics_settings',
			array(
				'tva_rate'            => 0.0,
				'coefficients_camion' => array( 'Camion benne' => 2.0 ),
				'zones_tarifaires'    => array(
					array( 'min' => 0, 'max' => PHP_INT_MAX, 'tarif' => 100, 'libelle' => 'Zone unique' ),
				),
			)
		);

		$pricing = new Pricing();
		$result  = $pricing->calculer( 'Dakar', 'Bambey', 'Camion benne' );

		$this->assertSame( 'Zone unique', $result['zone'] );
		$this->assertSame( 100, $result['tarifZone'] );
		$this->assertSame( 12000, $result['montantTransport'] );
		// Coefficient 2.0 => majoration = (2-1) * 100 * 120 = 12000.
		$this->assertSame( 12000, $result['majoration'] );
		$this->assertSame( 0, $result['tva'], 'TVA à 0% dans les réglages personnalisés' );

		update_option( 'sierra_logistics_settings', array() );
	}
}
