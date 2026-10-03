<?php
/**
 * Page "Tableau de bord" du back-office.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Admin;

use SierraLogistics\Formatting;
use SierraLogistics\Repositories\Commandes_Repository;
use SierraLogistics\Repositories\Quotes_Repository;
use SierraLogistics\Repositories\Vehicles_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vue d'ensemble de l'activité : devis du mois, en attente, commandes en
 * cours, CA facturé. Port de src/app/admin/(dashboard)/page.jsx.
 */
class Dashboard_Page {

	/**
	 * Affiche le tableau de bord.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'sierra_view_dashboard' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'sierra-logistics' ) );
		}

		$quotes_repository    = new Quotes_Repository();
		$vehicles_repository  = new Vehicles_Repository();
		$commandes_repository = new Commandes_Repository();

		$quotes    = $quotes_repository->all();
		$vehicles  = $vehicles_repository->all();
		$commandes = $commandes_repository->all();

		$en_attente  = count( array_filter( $quotes, static fn( $q ) => 'en_attente' === $q['statut'] ) );
		$commandees  = count( array_filter( $quotes, static fn( $q ) => 'commandé' === $q['statut'] ) );
		$ca          = array_sum(
			array_map(
				static fn( $q ) => (float) $q['total'],
				array_filter( $quotes, static fn( $q ) => 'commandé' === $q['statut'] )
			)
		);
		$disponibles = count( array_filter( $vehicles, static fn( $v ) => 'disponible' === $v['status'] ) );
		$recent      = array_slice( $quotes, 0, 8 );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Tableau de bord', 'sierra-logistics' ); ?></h1>
			<p><?php esc_html_e( "Vue d'ensemble de l'activité Sierra Logistics.", 'sierra-logistics' ); ?></p>

			<div class="sierra-stat-grid">
				<?php
				self::stat_card( __( 'Devis reçus', 'sierra-logistics' ), number_format_i18n( count( $quotes ) ) );
				self::stat_card( __( 'Devis en attente', 'sierra-logistics' ), number_format_i18n( $en_attente ) );
				self::stat_card( __( 'Commandes validées', 'sierra-logistics' ), number_format_i18n( count( $commandes ) ) );
				self::stat_card( __( "Chiffre d'affaires (validé)", 'sierra-logistics' ), Formatting::fcfa( $ca ) );
				self::stat_card( __( 'Camions enregistrés', 'sierra-logistics' ), number_format_i18n( count( $vehicles ) ) );
				self::stat_card(
					__( 'Camions disponibles', 'sierra-logistics' ),
					number_format_i18n( $disponibles ),
					/* translators: %s: nombre de camions en course ou en maintenance */
					sprintf( __( '%s en course ou en maintenance', 'sierra-logistics' ), number_format_i18n( count( $vehicles ) - $disponibles ) )
				);
				?>
			</div>

			<h2><?php esc_html_e( 'Derniers devis', 'sierra-logistics' ); ?></h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Client', 'sierra-logistics' ); ?></th>
						<th><?php esc_html_e( 'Trajet', 'sierra-logistics' ); ?></th>
						<th><?php esc_html_e( 'Montant', 'sierra-logistics' ); ?></th>
						<th><?php esc_html_e( 'Statut', 'sierra-logistics' ); ?></th>
						<th><?php esc_html_e( 'Date', 'sierra-logistics' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $recent ) ) : ?>
						<tr><td colspan="5"><?php esc_html_e( 'Aucun devis pour le moment.', 'sierra-logistics' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $recent as $quote ) : ?>
							<tr>
								<td>
									<a href="<?php echo esc_url( Quotes_Page::detail_url( $quote['id'] ) ); ?>">
										<?php echo esc_html( $quote['nom'] ? $quote['nom'] : __( 'Client', 'sierra-logistics' ) ); ?>
									</a>
								</td>
								<td><?php echo esc_html( $quote['ville_depart'] . ' → ' . $quote['ville_arrivee'] ); ?></td>
								<td><?php echo esc_html( Formatting::fcfa( $quote['total'] ) ); ?></td>
								<td>
									<?php if ( 'commandé' === $quote['statut'] ) : ?>
										<span class="sierra-badge sierra-badge--success"><?php esc_html_e( 'Commandé', 'sierra-logistics' ); ?></span>
									<?php else : ?>
										<span class="sierra-badge sierra-badge--warning"><?php esc_html_e( 'En attente', 'sierra-logistics' ); ?></span>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( Formatting::to_dakar_time( $quote['created_at'], 'd/m/Y' ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Affiche une carte de statistique.
	 *
	 * @param string      $label Libellé de la statistique.
	 * @param string      $value Valeur déjà formatée.
	 * @param string|null $hint  Précision optionnelle affichée sous la valeur.
	 */
	private static function stat_card( string $label, string $value, ?string $hint = null ): void {
		?>
		<div class="sierra-stat-card">
			<p class="sierra-stat-card__label"><?php echo esc_html( $label ); ?></p>
			<p class="sierra-stat-card__value"><?php echo esc_html( $value ); ?></p>
			<?php if ( $hint ) : ?>
				<p class="sierra-stat-card__hint"><?php echo esc_html( $hint ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}
}
