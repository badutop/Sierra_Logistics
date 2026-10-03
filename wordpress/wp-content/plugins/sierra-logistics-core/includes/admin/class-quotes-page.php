<?php
/**
 * Page "Devis" du back-office (liste + fiche détaillée).
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Admin;

use SierraLogistics\Formatting;
use SierraLogistics\Invoice_Pdf;
use SierraLogistics\Pricing;
use SierraLogistics\Settings;
use SierraLogistics\Repositories\Commandes_Repository;
use SierraLogistics\Repositories\Quote_History_Repository;
use SierraLogistics\Repositories\Quotes_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Liste des devis (filtres, recherche) et fiche détaillée (recalcul,
 * changement de statut, historique). Port de
 * src/app/admin/(dashboard)/factures/page.jsx et factures/[id]/page.jsx.
 */
class Quotes_Page {

	/**
	 * Branche les actions de formulaire (admin-post.php).
	 */
	public static function register(): void {
		add_action( 'admin_post_sierra_quote_set_status', array( __CLASS__, 'handle_set_status' ) );
		add_action( 'admin_post_sierra_quote_recalculate', array( __CLASS__, 'handle_recalculate' ) );
		add_action( 'admin_post_sierra_quote_download_pdf', array( __CLASS__, 'handle_download_pdf' ) );
		add_action( 'admin_post_sierra_quote_send_email', array( __CLASS__, 'handle_send_email' ) );
		add_action( 'admin_post_sierra_quotes_export_csv', array( __CLASS__, 'handle_export_csv' ) );
	}

	/**
	 * URL de la fiche détaillée d'un devis.
	 *
	 * @param string $id UUID du devis.
	 */
	public static function detail_url( string $id ): string {
		return admin_url( 'admin.php?page=' . Menu::SLUG_QUOTES . '&action=view&id=' . rawurlencode( $id ) );
	}

	/**
	 * URL d'une action de ligne avec nonce (ex. suppression).
	 *
	 * @param string $action Action demandée.
	 * @param string $id     UUID du devis concerné.
	 */
	public static function action_url( string $action, string $id ): string {
		$url = admin_url( 'admin.php?page=' . Menu::SLUG_QUOTES . '&action=' . $action . '&id=' . rawurlencode( $id ) );
		return wp_nonce_url( $url, 'sierra_quote_' . $action . '_' . $id );
	}

	/**
	 * Affiche la liste ou la fiche détaillée selon les paramètres de la requête.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'sierra_manage_quotes' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'sierra-logistics' ) );
		}

		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- aiguillage de lecture, les actions de modification vérifient leur propre nonce.
		$id     = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'delete' === $action && $id ) {
			self::handle_delete( $id );
			return;
		}

		if ( 'view' === $action && $id ) {
			self::render_detail( $id );
			return;
		}

		self::render_list();
	}

	/**
	 * Affiche la liste des devis avec ses filtres.
	 */
	private static function render_list(): void {
		$table = new Quotes_List_Table();
		$table->prepare_items();

		$statut    = isset( $_GET['statut'] ) ? sanitize_text_field( wp_unslash( $_GET['statut'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$date_from = isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$date_to   = isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		?>
		<div class="wrap">
			<h1>
				<?php esc_html_e( 'Devis', 'sierra-logistics' ); ?>
				<?php if ( current_user_can( 'sierra_import_export' ) ) : ?>
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=sierra_quotes_export_csv' ), 'sierra_quotes_export_csv' ) ); ?>" class="page-title-action">
						<?php esc_html_e( 'Exporter en CSV', 'sierra-logistics' ); ?>
					</a>
				<?php endif; ?>
			</h1>

			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( Menu::SLUG_QUOTES ); ?>" />
				<p class="search-box">
					<label class="screen-reader-text" for="sierra-search"><?php esc_html_e( 'Rechercher', 'sierra-logistics' ); ?></label>
					<?php $search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- filtre de lecture (GET), pas une action qui modifie des données. ?>
					<input type="search" id="sierra-search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Nom, téléphone, email, ville...', 'sierra-logistics' ); ?>" />
					<select name="statut">
						<option value=""><?php esc_html_e( 'Tous les statuts', 'sierra-logistics' ); ?></option>
						<option value="en_attente" <?php selected( $statut, 'en_attente' ); ?>><?php esc_html_e( 'En attente', 'sierra-logistics' ); ?></option>
						<option value="commandé" <?php selected( $statut, 'commandé' ); ?>><?php esc_html_e( 'Commandé', 'sierra-logistics' ); ?></option>
					</select>
					<input type="date" name="date_from" value="<?php echo esc_attr( $date_from ); ?>" />
					<input type="date" name="date_to" value="<?php echo esc_attr( $date_to ); ?>" />
					<?php submit_button( __( 'Filtrer', 'sierra-logistics' ), '', '', false ); ?>
				</p>
			</form>

			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( Menu::SLUG_QUOTES ); ?>" />
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Affiche la fiche détaillée d'un devis.
	 *
	 * @param string $id UUID du devis.
	 */
	private static function render_detail( string $id ): void {
		$quotes_repository = new Quotes_Repository();
		$quote             = $quotes_repository->find( $id );

		if ( ! $quote ) {
			echo '<div class="wrap"><h1>' . esc_html__( 'Devis', 'sierra-logistics' ) . '</h1>';
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Devis introuvable.', 'sierra-logistics' ) . '</p></div></div>';
			return;
		}

		$commande = ( new Commandes_Repository() )->find_by_proforma( $id );
		$history  = ( new Quote_History_Repository() )->for_quote( $id );
		$notice   = isset( $_GET['updated'] ) ? sanitize_key( wp_unslash( $_GET['updated'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- affichage d'une notice de confirmation, aucune donnée modifiée ici.

		?>
		<div class="wrap">
			<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Menu::SLUG_QUOTES ) ); ?>">&larr; <?php esc_html_e( 'Retour aux devis', 'sierra-logistics' ); ?></a></p>
			<h1><?php echo esc_html( $quote['nom'] ? $quote['nom'] : __( 'Client', 'sierra-logistics' ) ); ?></h1>

			<?php if ( 'recalculated' === $notice ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Devis recalculé.', 'sierra-logistics' ); ?></p></div>
			<?php elseif ( 'status' === $notice ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Statut mis à jour.', 'sierra-logistics' ); ?></p></div>
			<?php elseif ( 'sent' === $notice ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'E-mail envoyé avec la facture en pièce jointe.', 'sierra-logistics' ); ?></p></div>
			<?php endif; ?>

			<div class="sierra-detail-grid">
				<div class="sierra-detail-card">
					<h2><?php esc_html_e( 'Détails', 'sierra-logistics' ); ?></h2>
					<table class="form-table">
						<tr><th><?php esc_html_e( 'N° de facture', 'sierra-logistics' ); ?></th><td><?php echo esc_html( $quote['invoice_number'] ? $quote['invoice_number'] : '-' ); ?><?php echo $commande && $commande['invoice_number'] ? ' / ' . esc_html( $commande['invoice_number'] ) . ' (' . esc_html__( 'définitive', 'sierra-logistics' ) . ')' : ''; ?></td></tr>
						<tr><th><?php esc_html_e( 'Téléphone', 'sierra-logistics' ); ?></th><td><?php echo esc_html( $quote['telephone'] ); ?></td></tr>
						<tr><th><?php esc_html_e( 'Email', 'sierra-logistics' ); ?></th><td><?php echo esc_html( $quote['email'] ); ?></td></tr>
						<tr><th><?php esc_html_e( 'Trajet', 'sierra-logistics' ); ?></th><td><?php echo esc_html( $quote['ville_depart'] . ' → ' . $quote['ville_arrivee'] ); ?></td></tr>
						<tr><th><?php esc_html_e( 'Distance', 'sierra-logistics' ); ?></th><td><?php echo esc_html( $quote['distance'] ? $quote['distance'] . ' km' : '-' ); ?></td></tr>
						<tr><th><?php esc_html_e( 'Type de marchandise', 'sierra-logistics' ); ?></th><td><?php echo esc_html( $quote['type_marchandise'] ); ?></td></tr>
						<tr><th><?php esc_html_e( 'Poids', 'sierra-logistics' ); ?></th><td><?php echo esc_html( $quote['poids'] ? $quote['poids'] . ' kg' : '-' ); ?></td></tr>
						<tr><th><?php esc_html_e( 'Type de camion', 'sierra-logistics' ); ?></th><td><?php echo esc_html( $quote['type_vehicle'] ); ?></td></tr>
						<tr><th><?php esc_html_e( 'Date d\'expédition', 'sierra-logistics' ); ?></th><td><?php echo esc_html( $quote['date_expedition'] ); ?></td></tr>
						<tr><th><?php esc_html_e( 'Infos complémentaires', 'sierra-logistics' ); ?></th><td><?php echo esc_html( $quote['infos_additionnelles'] ); ?></td></tr>
						<tr><th><?php esc_html_e( 'Transport base', 'sierra-logistics' ); ?></th><td><?php echo esc_html( Formatting::fcfa( $quote['montant_transport'] ) ); ?></td></tr>
						<tr><th><?php esc_html_e( 'Majoration', 'sierra-logistics' ); ?></th><td><?php echo esc_html( Formatting::fcfa( $quote['majoration'] ) ); ?></td></tr>
						<tr><th><?php esc_html_e( 'Sous-total', 'sierra-logistics' ); ?></th><td><?php echo esc_html( Formatting::fcfa( $quote['sous_total'] ) ); ?></td></tr>
						<tr><th><?php esc_html_e( 'TVA', 'sierra-logistics' ); ?></th><td><?php echo esc_html( Formatting::fcfa( $quote['tva'] ) ); ?></td></tr>
						<tr><th><strong><?php esc_html_e( 'Total', 'sierra-logistics' ); ?></strong></th><td><strong><?php echo esc_html( Formatting::fcfa( $quote['total'] ) ); ?></strong></td></tr>
					</table>

					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:1em;">
						<?php wp_nonce_field( 'sierra_quote_recalculate_' . $id ); ?>
						<input type="hidden" name="action" value="sierra_quote_recalculate" />
						<input type="hidden" name="id" value="<?php echo esc_attr( $id ); ?>" />
						<?php submit_button( __( 'Recalculer le devis', 'sierra-logistics' ), 'secondary', 'submit', false ); ?>
						<p class="description"><?php esc_html_e( 'Relance le calcul tarifaire avec les villes/camion actuels et les réglages en vigueur.', 'sierra-logistics' ); ?></p>
					</form>

					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:1em;">
						<?php wp_nonce_field( 'sierra_quote_set_status_' . $id ); ?>
						<input type="hidden" name="action" value="sierra_quote_set_status" />
						<input type="hidden" name="id" value="<?php echo esc_attr( $id ); ?>" />
						<select name="statut">
							<option value="en_attente" <?php selected( $quote['statut'], 'en_attente' ); ?>><?php esc_html_e( 'En attente', 'sierra-logistics' ); ?></option>
							<option value="commandé" <?php selected( $quote['statut'], 'commandé' ); ?>><?php esc_html_e( 'Commandé', 'sierra-logistics' ); ?></option>
						</select>
						<?php submit_button( __( 'Changer le statut', 'sierra-logistics' ), 'secondary', 'submit', false ); ?>
					</form>

					<p style="margin-top:1em;">
						<a class="button" href="<?php echo esc_url( home_url( '/facture-proforma?id=' . $id . ( $commande ? '&definitive=true' : '' ) ) ); ?>" target="_blank">
							<?php esc_html_e( 'Ouvrir la facture', 'sierra-logistics' ); ?>
						</a>
						<?php if ( current_user_can( 'sierra_manage_invoices' ) ) : ?>
							<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=sierra_quote_download_pdf&id=' . rawurlencode( $id ) ), 'sierra_quote_pdf_' . $id ) ); ?>">
								<?php esc_html_e( 'Télécharger le PDF', 'sierra-logistics' ); ?>
							</a>
						<?php endif; ?>
						<?php if ( ! $commande && current_user_can( 'sierra_validate_commande' ) ) : ?>
							<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=' . Menu::SLUG_COMMANDES . '&action=validate&id=' . rawurlencode( $id ) ) ); ?>">
								<?php esc_html_e( 'Valider en commande', 'sierra-logistics' ); ?>
							</a>
						<?php endif; ?>
					</p>

					<?php if ( current_user_can( 'sierra_manage_invoices' ) ) : ?>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:1em;">
							<?php wp_nonce_field( 'sierra_quote_send_email_' . $id ); ?>
							<input type="hidden" name="action" value="sierra_quote_send_email" />
							<input type="hidden" name="id" value="<?php echo esc_attr( $id ); ?>" />
							<label for="email"><?php esc_html_e( 'Envoyer la facture PDF par e-mail à :', 'sierra-logistics' ); ?></label>
							<input type="email" id="email" name="email" value="<?php echo esc_attr( $quote['email'] ); ?>" required />
							<?php submit_button( __( 'Envoyer', 'sierra-logistics' ), 'secondary', 'submit', false ); ?>
							<?php if ( 'sent' === $notice ) : ?>
								<span class="description" style="color:#00a32a;"><?php esc_html_e( 'E-mail envoyé.', 'sierra-logistics' ); ?></span>
							<?php endif; ?>
						</form>
					<?php endif; ?>
				</div>

				<div class="sierra-detail-card">
					<h2><?php esc_html_e( 'Historique', 'sierra-logistics' ); ?></h2>
					<?php if ( empty( $history ) ) : ?>
						<p class="description"><?php esc_html_e( 'Aucun événement enregistré pour ce devis.', 'sierra-logistics' ); ?></p>
					<?php else : ?>
						<ul class="sierra-history">
							<?php foreach ( $history as $entry ) : ?>
								<li>
									<strong><?php echo esc_html( Formatting::to_dakar_time( $entry['created_at'], 'd/m/Y H:i' ) ); ?></strong>
									&mdash; <?php echo esc_html( $entry['message'] ); ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Traite la suppression d'un devis (lien de ligne avec nonce).
	 *
	 * @param string $id UUID du devis.
	 */
	private static function handle_delete( string $id ): void {
		check_admin_referer( 'sierra_quote_delete_' . $id );

		if ( ! current_user_can( 'sierra_delete_records' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'sierra-logistics' ) );
		}

		( new Quotes_Repository() )->delete( $id );

		wp_safe_redirect( admin_url( 'admin.php?page=' . Menu::SLUG_QUOTES . '&deleted=1' ) );
		exit;
	}

	/**
	 * Traite le changement de statut manuel depuis la fiche détaillée.
	 */
	public static function handle_set_status(): void {
		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		check_admin_referer( 'sierra_quote_set_status_' . $id );

		if ( ! current_user_can( 'sierra_manage_quotes' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'sierra-logistics' ) );
		}

		$statut = isset( $_POST['statut'] ) ? sanitize_text_field( wp_unslash( $_POST['statut'] ) ) : '';
		if ( ! in_array( $statut, Quotes_Repository::STATUSES, true ) ) {
			wp_die( esc_html__( 'Statut invalide.', 'sierra-logistics' ) );
		}

		$quotes_repository = new Quotes_Repository();
		$quote             = $quotes_repository->find( $id );
		if ( ! $quote ) {
			wp_die( esc_html__( 'Devis introuvable.', 'sierra-logistics' ) );
		}

		$quotes_repository->update( $id, array( 'statut' => $statut ) );

		( new Quote_History_Repository() )->log(
			$id,
			sprintf(
				/* translators: 1: ancien statut, 2: nouveau statut */
				__( 'Statut changé manuellement de "%1$s" à "%2$s".', 'sierra-logistics' ),
				$quote['statut'],
				$statut
			),
			get_current_user_id() ? get_current_user_id() : null
		);

		wp_safe_redirect( self::detail_url( $id ) . '&updated=status' );
		exit;
	}

	/**
	 * Traite le recalcul manuel d'un devis.
	 */
	public static function handle_recalculate(): void {
		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		check_admin_referer( 'sierra_quote_recalculate_' . $id );

		if ( ! current_user_can( 'sierra_manage_quotes' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'sierra-logistics' ) );
		}

		$quotes_repository = new Quotes_Repository();
		$quote             = $quotes_repository->find( $id );
		if ( ! $quote ) {
			wp_die( esc_html__( 'Devis introuvable.', 'sierra-logistics' ) );
		}

		try {
			$pricing = new Pricing();
			$calcul  = $pricing->calculer( $quote['ville_depart'], $quote['ville_arrivee'], $quote['type_vehicle'] );
		} catch ( \InvalidArgumentException $e ) {
			wp_die( esc_html( $e->getMessage() ) );
		}

		$quotes_repository->update(
			$id,
			array(
				'distance'           => $calcul['distance'],
				'zone'               => $calcul['zone'],
				'tarif_zone'         => $calcul['tarifZone'],
				'coefficient_camion' => $calcul['coefficientCamion'],
				'montant_transport'  => $calcul['montantTransport'],
				'majoration'         => $calcul['majoration'],
				'sous_total'         => $calcul['sousTotal'],
				'tva'                => $calcul['tva'],
				'total'              => $calcul['total'],
			)
		);

		( new Quote_History_Repository() )->log(
			$id,
			sprintf(
				/* translators: %s: nouveau montant total */
				__( 'Devis recalculé : nouveau total %s.', 'sierra-logistics' ),
				Formatting::fcfa( $calcul['total'] )
			),
			get_current_user_id() ? get_current_user_id() : null
		);

		wp_safe_redirect( self::detail_url( $id ) . '&updated=recalculated' );
		exit;
	}

	/**
	 * Génère et envoie au téléchargement le PDF d'un devis (proforma ou
	 * définitive selon qu'une commande existe).
	 */
	public static function handle_download_pdf(): void {
		$id = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
		check_admin_referer( 'sierra_quote_pdf_' . $id );

		if ( ! current_user_can( 'sierra_manage_invoices' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'sierra-logistics' ) );
		}

		$quote = ( new Quotes_Repository() )->find( $id );
		if ( ! $quote ) {
			wp_die( esc_html__( 'Devis introuvable.', 'sierra-logistics' ) );
		}
		$commande = ( new Commandes_Repository() )->find_by_proforma( $id );

		try {
			$pdf = Invoice_Pdf::render( $quote, $commande );
		} catch ( \RuntimeException $e ) {
			wp_die( esc_html( $e->getMessage() ) );
		}

		nocache_headers();
		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: inline; filename="' . Invoice_Pdf::filename( $quote, $commande ) . '"' );
		header( 'Content-Length: ' . strlen( $pdf ) );
		echo $pdf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- contenu binaire PDF, pas du HTML.
		exit;
	}

	/**
	 * Envoie la facture PDF d'un devis par e-mail, en pièce jointe.
	 */
	public static function handle_send_email(): void {
		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		check_admin_referer( 'sierra_quote_send_email_' . $id );

		if ( ! current_user_can( 'sierra_manage_invoices' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'sierra-logistics' ) );
		}

		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( ! is_email( $email ) ) {
			wp_die( esc_html__( 'Adresse e-mail invalide.', 'sierra-logistics' ) );
		}

		$quote = ( new Quotes_Repository() )->find( $id );
		if ( ! $quote ) {
			wp_die( esc_html__( 'Devis introuvable.', 'sierra-logistics' ) );
		}
		$commande      = ( new Commandes_Repository() )->find_by_proforma( $id );
		$is_definitive = (bool) $commande;

		try {
			$tmp_file = Invoice_Pdf::write_temp_file( $quote, $commande );
		} catch ( \RuntimeException $e ) {
			wp_die( esc_html( $e->getMessage() ) );
		}

		$settings = Settings::all();
		$headers  = array();
		if ( $settings['email_from_address'] ) {
			$headers[] = sprintf( 'From: %s <%s>', $settings['email_from_name'], $settings['email_from_address'] );
		}

		$document_label = $is_definitive ? __( 'facture définitive', 'sierra-logistics' ) : __( 'facture proforma', 'sierra-logistics' );
		$subject        = ucfirst( $document_label ) . ' - ' . $settings['company_name'];
		$body           = sprintf(
			/* translators: 1: nom du client, 2: type de document, 3: ville de départ, 4: ville d'arrivée, 5: nom de l'entreprise */
			__( "Bonjour %1\$s,\n\nVeuillez trouver ci-joint votre %2\$s pour le trajet %3\$s vers %4\$s.\n\n%5\$s", 'sierra-logistics' ),
			$quote['nom'],
			$document_label,
			$quote['ville_depart'],
			$quote['ville_arrivee'],
			$settings['company_name']
		);

		$sent = wp_mail( $email, $subject, $body, $headers, array( $tmp_file ) );

		wp_delete_file( $tmp_file );

		if ( ! $sent ) {
			wp_die( esc_html__( "L'envoi de l'e-mail a échoué.", 'sierra-logistics' ) );
		}

		( new Quote_History_Repository() )->log(
			$id,
			sprintf(
				/* translators: %s: adresse e-mail du destinataire */
				__( 'Facture envoyée par e-mail à %s.', 'sierra-logistics' ),
				$email
			),
			get_current_user_id() ? get_current_user_id() : null
		);

		wp_safe_redirect( self::detail_url( $id ) . '&updated=sent' );
		exit;
	}

	/**
	 * Exporte tous les devis au format CSV.
	 */
	public static function handle_export_csv(): void {
		check_admin_referer( 'sierra_quotes_export_csv' );

		if ( ! current_user_can( 'sierra_import_export' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'sierra-logistics' ) );
		}

		$quotes = ( new Quotes_Repository() )->all();

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="devis-' . gmdate( 'Y-m-d' ) . '.csv"' );

		$output = fopen( 'php://output', 'w' );
		fputcsv(
			$output,
			array(
				'invoice_number',
				'nom',
				'email',
				'telephone',
				'ville_depart',
				'ville_arrivee',
				'type_marchandise',
				'poids',
				'type_vehicle',
				'date_expedition',
				'distance',
				'total',
				'statut',
				'created_at',
			)
		);

		foreach ( $quotes as $quote ) {
			fputcsv(
				$output,
				array(
					$quote['invoice_number'],
					$quote['nom'],
					$quote['email'],
					$quote['telephone'],
					$quote['ville_depart'],
					$quote['ville_arrivee'],
					$quote['type_marchandise'],
					$quote['poids'],
					$quote['type_vehicle'],
					$quote['date_expedition'],
					$quote['distance'],
					$quote['total'],
					$quote['statut'],
					$quote['created_at'],
				)
			);
		}

		fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- flux php://output, pas un fichier : WP_Filesystem ne sait pas écrire dans un flux de sortie HTTP.
		exit;
	}
}
