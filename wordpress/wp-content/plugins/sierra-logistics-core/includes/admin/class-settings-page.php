<?php
/**
 * Page "Réglages" du back-office.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Admin;

use SierraLogistics\Pricing;
use SierraLogistics\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Réglages tarifaires (zones, coefficients, TVA), coordonnées de
 * l'entreprise pour les PDF, et paramètres de l'API publique (CORS,
 * expéditeur e-mail, limite de fréquence). Tout ce que le cahier des charges
 * demande de sortir du code en dur (voir migration/DEPLOIEMENT.md).
 */
class Settings_Page {

	const NONCE_ACTION = 'sierra_settings_save';

	/**
	 * Branche le traitement du formulaire (admin-post.php).
	 */
	public static function register(): void {
		add_action( 'admin_post_sierra_settings_save', array( __CLASS__, 'handle_save' ) );
	}

	/**
	 * Affiche le formulaire de réglages.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'sierra_manage_settings' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'sierra-logistics' ) );
		}

		$settings = Settings::all();
		$saved    = isset( $_GET['saved'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- affichage d'une notice de confirmation, aucune donnée modifiée ici.

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Réglages Sierra Logistics', 'sierra-logistics' ); ?></h1>

			<?php if ( $saved ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Réglages enregistrés.', 'sierra-logistics' ); ?></p></div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( self::NONCE_ACTION ); ?>
				<input type="hidden" name="action" value="sierra_settings_save" />

				<h2><?php esc_html_e( 'Zones tarifaires', 'sierra-logistics' ); ?></h2>
				<table class="widefat">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Libellé', 'sierra-logistics' ); ?></th>
							<th><?php esc_html_e( 'Distance min (km)', 'sierra-logistics' ); ?></th>
							<th><?php esc_html_e( 'Distance max (km, vide = illimité)', 'sierra-logistics' ); ?></th>
							<th><?php esc_html_e( 'Tarif (FCFA/km)', 'sierra-logistics' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $settings['zones_tarifaires'] as $i => $zone ) : ?>
							<tr>
								<td><input type="text" name="zones[<?php echo (int) $i; ?>][libelle]" value="<?php echo esc_attr( $zone['libelle'] ); ?>" class="regular-text" /></td>
								<td><input type="number" name="zones[<?php echo (int) $i; ?>][min]" value="<?php echo esc_attr( $zone['min'] ); ?>" min="0" /></td>
								<td><input type="number" name="zones[<?php echo (int) $i; ?>][max]" value="<?php echo esc_attr( PHP_INT_MAX === $zone['max'] ? '' : $zone['max'] ); ?>" min="0" /></td>
								<td><input type="number" name="zones[<?php echo (int) $i; ?>][tarif]" value="<?php echo esc_attr( $zone['tarif'] ); ?>" min="0" /></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<h2><?php esc_html_e( 'Coefficients par type de camion', 'sierra-logistics' ); ?></h2>
				<table class="widefat">
					<thead><tr><th><?php esc_html_e( 'Type de camion', 'sierra-logistics' ); ?></th><th><?php esc_html_e( 'Coefficient', 'sierra-logistics' ); ?></th></tr></thead>
					<tbody>
						<?php foreach ( $settings['coefficients_camion'] as $type => $coefficient ) : ?>
							<tr>
								<td><?php echo esc_html( $type ); ?></td>
								<td><input type="number" step="0.1" min="0" name="coefficients[<?php echo esc_attr( $type ); ?>]" value="<?php echo esc_attr( $coefficient ); ?>" /></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<h2><?php esc_html_e( 'TVA', 'sierra-logistics' ); ?></h2>
				<table class="form-table">
					<tr>
						<th><label for="tva_rate"><?php esc_html_e( 'Taux de TVA (%)', 'sierra-logistics' ); ?></label></th>
						<td><input type="number" step="0.01" min="0" max="100" id="tva_rate" name="tva_rate" value="<?php echo esc_attr( $settings['tva_rate'] * 100 ); ?>" /></td>
					</tr>
				</table>

				<h2><?php esc_html_e( "Coordonnées de l'entreprise (en-tête des PDF)", 'sierra-logistics' ); ?></h2>
				<table class="form-table">
					<tr><th><label for="company_name"><?php esc_html_e( 'Nom', 'sierra-logistics' ); ?></label></th><td><input type="text" id="company_name" name="company_name" class="regular-text" value="<?php echo esc_attr( $settings['company_name'] ); ?>" /></td></tr>
					<tr><th><label for="company_address"><?php esc_html_e( 'Adresse', 'sierra-logistics' ); ?></label></th><td><input type="text" id="company_address" name="company_address" class="regular-text" value="<?php echo esc_attr( $settings['company_address'] ); ?>" /></td></tr>
					<tr><th><label for="company_phone"><?php esc_html_e( 'Téléphone', 'sierra-logistics' ); ?></label></th><td><input type="text" id="company_phone" name="company_phone" class="regular-text" value="<?php echo esc_attr( $settings['company_phone'] ); ?>" /></td></tr>
					<tr><th><label for="company_ninea"><?php esc_html_e( 'NINEA / identifiant fiscal', 'sierra-logistics' ); ?></label></th><td><input type="text" id="company_ninea" name="company_ninea" class="regular-text" value="<?php echo esc_attr( $settings['company_ninea'] ); ?>" /></td></tr>
				</table>

				<h2><?php esc_html_e( 'Numérotation des factures', 'sierra-logistics' ); ?></h2>
				<table class="form-table">
					<tr><th><label for="invoice_prefix_proforma"><?php esc_html_e( 'Préfixe proforma', 'sierra-logistics' ); ?></label></th><td><input type="text" id="invoice_prefix_proforma" name="invoice_prefix_proforma" value="<?php echo esc_attr( $settings['invoice_prefix_proforma'] ); ?>" /></td></tr>
					<tr><th><label for="invoice_prefix_definitive"><?php esc_html_e( 'Préfixe facture définitive', 'sierra-logistics' ); ?></label></th><td><input type="text" id="invoice_prefix_definitive" name="invoice_prefix_definitive" value="<?php echo esc_attr( $settings['invoice_prefix_definitive'] ); ?>" /></td></tr>
				</table>

				<h2><?php esc_html_e( 'E-mails', 'sierra-logistics' ); ?></h2>
				<table class="form-table">
					<tr><th><label for="email_from_name"><?php esc_html_e( "Nom de l'expéditeur", 'sierra-logistics' ); ?></label></th><td><input type="text" id="email_from_name" name="email_from_name" class="regular-text" value="<?php echo esc_attr( $settings['email_from_name'] ); ?>" /></td></tr>
					<tr><th><label for="email_from_address"><?php esc_html_e( "Adresse e-mail de l'expéditeur", 'sierra-logistics' ); ?></label></th><td><input type="email" id="email_from_address" name="email_from_address" class="regular-text" value="<?php echo esc_attr( $settings['email_from_address'] ); ?>" /><p class="description"><?php esc_html_e( 'Doit correspondre à un domaine autorisé à envoyer au nom de ce site (SPF/DKIM), voir migration/DEPLOIEMENT.md.', 'sierra-logistics' ); ?></p></td></tr>
					<tr><th><label for="notification_email"><?php esc_html_e( "E-mail de notification de l'équipe", 'sierra-logistics' ); ?></label></th><td><input type="email" id="notification_email" name="notification_email" class="regular-text" value="<?php echo esc_attr( $settings['notification_email'] ); ?>" /></td></tr>
				</table>

				<h2><?php esc_html_e( 'API publique', 'sierra-logistics' ); ?></h2>
				<table class="form-table">
					<tr>
						<th><label for="cors_allowed_origins"><?php esc_html_e( 'Origines CORS autorisées', 'sierra-logistics' ); ?></label></th>
						<td>
							<textarea id="cors_allowed_origins" name="cors_allowed_origins" rows="3" class="large-text" placeholder="https://sierra-logistics.sn"><?php echo esc_textarea( implode( "\n", $settings['cors_allowed_origins'] ) ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Une URL par ligne (le domaine du front). localhost est toujours autorisé en développement.', 'sierra-logistics' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="rate_limit_per_hour"><?php esc_html_e( 'Limite de demandes de devis par IP et par heure', 'sierra-logistics' ); ?></label></th>
						<td><input type="number" min="1" id="rate_limit_per_hour" name="rate_limit_per_hour" value="<?php echo esc_attr( $settings['rate_limit_per_hour'] ); ?>" /></td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Traite l'enregistrement des réglages.
	 */
	public static function handle_save(): void {
		check_admin_referer( self::NONCE_ACTION );

		if ( ! current_user_can( 'sierra_manage_settings' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'sierra-logistics' ) );
		}

		$zones = array();
		if ( ! empty( $_POST['zones'] ) && is_array( $_POST['zones'] ) ) {
			foreach ( wp_unslash( $_POST['zones'] ) as $zone ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- chaque champ de $zone est sanitizé individuellement ci-dessous (sanitize_text_field, cast (int)).
				$zones[] = array(
					'libelle' => sanitize_text_field( $zone['libelle'] ?? '' ),
					'min'     => (int) ( $zone['min'] ?? 0 ),
					'max'     => '' === ( $zone['max'] ?? '' ) ? PHP_INT_MAX : (int) $zone['max'],
					'tarif'   => (int) ( $zone['tarif'] ?? 0 ),
				);
			}
			usort( $zones, static fn( $a, $b ) => $a['min'] <=> $b['min'] );
		}

		$coefficients = array();
		if ( ! empty( $_POST['coefficients'] ) && is_array( $_POST['coefficients'] ) ) {
			foreach ( wp_unslash( $_POST['coefficients'] ) as $type => $value ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- $type est sanitizé (sanitize_text_field) et $value casté (float) ci-dessous.
				$coefficients[ sanitize_text_field( $type ) ] = (float) $value;
			}
		}

		$origins = array();
		if ( ! empty( $_POST['cors_allowed_origins'] ) ) {
			$lines   = preg_split( '/[\r\n]+/', sanitize_textarea_field( wp_unslash( $_POST['cors_allowed_origins'] ) ) );
			$origins = array_values( array_filter( array_map( 'trim', $lines ) ) );
		}

		Settings::update(
			array(
				'zones_tarifaires'          => $zones ? $zones : Pricing::default_zones(),
				'coefficients_camion'       => $coefficients ? $coefficients : Pricing::default_coefficients(),
				'tva_rate'                  => isset( $_POST['tva_rate'] ) ? ( (float) wp_unslash( $_POST['tva_rate'] ) / 100 ) : Pricing::default_tva_rate(), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- cast (float) après wp_unslash().
				'cors_allowed_origins'      => $origins,
				'company_name'              => isset( $_POST['company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['company_name'] ) ) : '',
				'company_address'           => isset( $_POST['company_address'] ) ? sanitize_text_field( wp_unslash( $_POST['company_address'] ) ) : '',
				'company_phone'             => isset( $_POST['company_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['company_phone'] ) ) : '',
				'company_ninea'             => isset( $_POST['company_ninea'] ) ? sanitize_text_field( wp_unslash( $_POST['company_ninea'] ) ) : '',
				'invoice_prefix_proforma'   => isset( $_POST['invoice_prefix_proforma'] ) ? sanitize_text_field( wp_unslash( $_POST['invoice_prefix_proforma'] ) ) : 'PRO',
				'invoice_prefix_definitive' => isset( $_POST['invoice_prefix_definitive'] ) ? sanitize_text_field( wp_unslash( $_POST['invoice_prefix_definitive'] ) ) : 'FAC',
				'email_from_name'           => isset( $_POST['email_from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['email_from_name'] ) ) : '',
				'email_from_address'        => isset( $_POST['email_from_address'] ) ? sanitize_email( wp_unslash( $_POST['email_from_address'] ) ) : '',
				'notification_email'        => isset( $_POST['notification_email'] ) ? sanitize_email( wp_unslash( $_POST['notification_email'] ) ) : '',
				'rate_limit_per_hour'       => isset( $_POST['rate_limit_per_hour'] ) ? max( 1, (int) $_POST['rate_limit_per_hour'] ) : 10,
			)
		);

		wp_safe_redirect( admin_url( 'admin.php?page=' . Menu::SLUG_SETTINGS . '&saved=1' ) );
		exit;
	}
}
