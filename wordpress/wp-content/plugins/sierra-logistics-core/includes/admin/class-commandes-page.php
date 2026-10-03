<?php
/**
 * Page "Commandes" du back-office (liste, fiche, validation).
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Admin;

use SierraLogistics\Commande_Validator;
use SierraLogistics\Formatting;
use SierraLogistics\Repositories\Commandes_Repository;
use SierraLogistics\Repositories\Quotes_Repository;
use SierraLogistics\Repositories\Vehicles_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Liste des commandes validées, et formulaire de validation d'un devis en
 * commande (choix du véhicule). Port de
 * src/app/admin/(dashboard)/commandes/page.jsx et de la logique de
 * src/app/api/commandes/valider/route.js.
 */
class Commandes_Page {

	/**
	 * Branche l'action de formulaire de validation (admin-post.php).
	 */
	public static function register(): void {
		add_action( 'admin_post_sierra_commande_validate', array( __CLASS__, 'handle_validate' ) );
	}

	/**
	 * Affiche la liste, le formulaire de validation ou la fiche d'une commande.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'sierra_view_commandes' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'sierra-logistics' ) );
		}

		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- aiguillage de lecture, la validation vérifie son propre nonce.
		$id     = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'validate' === $action && $id && current_user_can( 'sierra_validate_commande' ) ) {
			self::render_validate_form( $id );
			return;
		}

		self::render_list();
	}

	/**
	 * Liste des commandes validées, avec le devis correspondant.
	 */
	private static function render_list(): void {
		$commandes         = ( new Commandes_Repository() )->all();
		$quotes_repository = new Quotes_Repository();
		$notice            = isset( $_GET['validated'] ) ? '1' : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- affichage d'une notice de confirmation, aucune donnée modifiée ici.

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Commandes', 'sierra-logistics' ); ?></h1>
			<p><?php esc_html_e( 'Commandes validées, avec le camion et le chauffeur affectés.', 'sierra-logistics' ); ?></p>

			<?php if ( $notice ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Commande validée.', 'sierra-logistics' ); ?></p></div>
			<?php endif; ?>

			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Client', 'sierra-logistics' ); ?></th>
						<th><?php esc_html_e( 'Trajet', 'sierra-logistics' ); ?></th>
						<th><?php esc_html_e( 'Camion', 'sierra-logistics' ); ?></th>
						<th><?php esc_html_e( 'Chauffeur', 'sierra-logistics' ); ?></th>
						<th><?php esc_html_e( 'Date validation', 'sierra-logistics' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $commandes ) ) : ?>
						<tr><td colspan="6"><?php esc_html_e( 'Aucune commande validée pour le moment.', 'sierra-logistics' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $commandes as $commande ) : ?>
							<?php $quote = $quotes_repository->find( $commande['proforma_id'] ); ?>
							<tr>
								<td>
									<strong><?php echo esc_html( $quote['nom'] ?? '-' ); ?></strong><br>
									<span class="description"><?php echo esc_html( $quote['telephone'] ?? '' ); ?></span>
								</td>
								<td><?php echo $quote ? esc_html( $quote['ville_depart'] . ' → ' . $quote['ville_arrivee'] ) : '-'; ?></td>
								<td><?php echo esc_html( $commande['camion_immatriculation'] ); ?></td>
								<td>
									<?php echo esc_html( $commande['chauffeur'] ); ?><br>
									<span class="description"><?php echo esc_html( $commande['telephone_chauffeur'] ); ?></span>
								</td>
								<td><?php echo esc_html( Formatting::to_dakar_time( $commande['date_validation'], 'd/m/Y H:i' ) ); ?></td>
								<td>
									<a href="<?php echo esc_url( Quotes_Page::detail_url( $commande['proforma_id'] ) ); ?>"><?php esc_html_e( 'Voir la facture', 'sierra-logistics' ); ?></a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Formulaire de validation d'un devis en commande : choix du véhicule.
	 *
	 * @param string $quote_id UUID du devis à valider.
	 */
	private static function render_validate_form( string $quote_id ): void {
		$quote = ( new Quotes_Repository() )->find( $quote_id );

		if ( ! $quote ) {
			echo '<div class="wrap"><div class="notice notice-error"><p>' . esc_html__( 'Devis introuvable.', 'sierra-logistics' ) . '</p></div></div>';
			return;
		}

		if ( 'en_attente' !== $quote['statut'] ) {
			echo '<div class="wrap"><div class="notice notice-error"><p>' . esc_html__( 'Ce devis a déjà été validé.', 'sierra-logistics' ) . '</p></div></div>';
			return;
		}

		$available = ( new Vehicles_Repository() )->all( array( 'status' => 'disponible' ) );

		?>
		<div class="wrap">
			<p><a href="<?php echo esc_url( Quotes_Page::detail_url( $quote_id ) ); ?>">&larr; <?php esc_html_e( 'Retour au devis', 'sierra-logistics' ); ?></a></p>
			<h1><?php esc_html_e( 'Valider la commande', 'sierra-logistics' ); ?></h1>
			<p>
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: nom du client, 2: ville de départ, 3: ville d'arrivée */
						__( 'Devis de %1$s : %2$s → %3$s', 'sierra-logistics' ),
						$quote['nom'],
						$quote['ville_depart'],
						$quote['ville_arrivee']
					)
				);
				?>
			</p>

			<?php if ( empty( $available ) ) : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'Aucun camion disponible actuellement.', 'sierra-logistics' ); ?></p></div>
			<?php else : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'sierra_commande_validate_' . $quote_id ); ?>
					<input type="hidden" name="action" value="sierra_commande_validate" />
					<input type="hidden" name="quote_id" value="<?php echo esc_attr( $quote_id ); ?>" />

					<table class="form-table">
						<tr>
							<th><label for="vehicle_id"><?php esc_html_e( 'Véhicule', 'sierra-logistics' ); ?></label></th>
							<td>
								<select name="vehicle_id" id="vehicle_id">
									<option value=""><?php esc_html_e( '-- Laisser le système choisir --', 'sierra-logistics' ); ?></option>
									<?php foreach ( $available as $vehicle ) : ?>
										<option value="<?php echo esc_attr( $vehicle['id'] ); ?>" <?php selected( $vehicle['model'], $quote['type_vehicle'] ); ?>>
											<?php echo esc_html( $vehicle['license_plate'] . ' — ' . $vehicle['name'] . ' (' . $vehicle['model'] . ')' ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Par défaut, priorité est donnée à un véhicule du type demandé par le devis.', 'sierra-logistics' ); ?></p>
							</td>
						</tr>
					</table>

					<?php submit_button( __( 'Valider la commande', 'sierra-logistics' ) ); ?>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Traite la validation d'un devis en commande.
	 */
	public static function handle_validate(): void {
		$quote_id = isset( $_POST['quote_id'] ) ? sanitize_text_field( wp_unslash( $_POST['quote_id'] ) ) : '';
		check_admin_referer( 'sierra_commande_validate_' . $quote_id );

		if ( ! current_user_can( 'sierra_validate_commande' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'sierra-logistics' ) );
		}

		$vehicle_id = isset( $_POST['vehicle_id'] ) && '' !== $_POST['vehicle_id']
			? sanitize_text_field( wp_unslash( $_POST['vehicle_id'] ) )
			: null;

		$result = Commande_Validator::validate( $quote_id, $vehicle_id );

		if ( is_wp_error( $result ) ) {
			wp_die( esc_html( $result->get_error_message() ) );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=' . Menu::SLUG_COMMANDES . '&validated=1' ) );
		exit;
	}
}
