<?php
/**
 * Page "Import" du back-office.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Admin;

use SierraLogistics\Importer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import des CSV exportés depuis Supabase, dans l'ordre
 * vehicles -> quotes -> commandes -> admins. Traité directement dans la
 * même requête (pas de redirection) : l'import est idempotent (voir
 * Importer), relancer la même page par erreur après un rechargement ne crée
 * donc jamais de doublon.
 */
class Import_Page {

	const NONCE_ACTION = 'sierra_import_run';

	/**
	 * Affiche la page et traite l'import si un formulaire a été soumis.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'sierra_import_export' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'sierra-logistics' ) );
		}

		$reports = null;
		if ( ! empty( $_POST['sierra_import_submit'] ) ) {
			check_admin_referer( self::NONCE_ACTION );
			$reports = self::process_upload();
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Import Supabase', 'sierra-logistics' ); ?></h1>
			<p>
				<?php esc_html_e( 'Chargez les CSV exportés depuis Supabase (Table Editor > Export CSV). Traitement dans l\'ordre véhicules, devis, commandes, admins, quels que soient les champs remplis. Relancer l\'import avec le même export ne crée jamais de doublon.', 'sierra-logistics' ); ?>
			</p>

			<?php if ( null !== $reports ) : ?>
				<div class="notice notice-info">
					<p><strong><?php esc_html_e( "Résultat de l'import", 'sierra-logistics' ); ?></strong></p>
					<?php foreach ( $reports as $table => $report ) : ?>
						<p>
							<?php
							echo esc_html(
								sprintf(
									/* translators: 1: nom de la table, 2: lignes importées, 3: lignes ignorées */
									__( '%1$s : %2$d importées, %3$d ignorées', 'sierra-logistics' ),
									$table,
									$report['imported'],
									$report['skipped']
								)
							);
							?>
						</p>
						<?php if ( ! empty( $report['errors'] ) ) : ?>
							<ul>
								<?php foreach ( $report['errors'] as $error ) : ?>
									<li><?php echo esc_html( $error ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<form method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( self::NONCE_ACTION ); ?>
				<table class="form-table">
					<tr>
						<th><label for="vehicles_csv"><?php esc_html_e( 'vehicles.csv', 'sierra-logistics' ); ?></label></th>
						<td><input type="file" id="vehicles_csv" name="vehicles_csv" accept=".csv" /></td>
					</tr>
					<tr>
						<th><label for="quotes_csv"><?php esc_html_e( 'quotes.csv', 'sierra-logistics' ); ?></label></th>
						<td><input type="file" id="quotes_csv" name="quotes_csv" accept=".csv" /></td>
					</tr>
					<tr>
						<th><label for="commandes_csv"><?php esc_html_e( 'commandes.csv', 'sierra-logistics' ); ?></label></th>
						<td><input type="file" id="commandes_csv" name="commandes_csv" accept=".csv" /></td>
					</tr>
					<tr>
						<th><label for="admins_csv"><?php esc_html_e( 'admins.csv', 'sierra-logistics' ); ?></label></th>
						<td>
							<input type="file" id="admins_csv" name="admins_csv" accept=".csv" />
							<p class="description"><?php esc_html_e( 'Colonnes attendues : id, name, role, created_at.', 'sierra-logistics' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="admin_emails_csv"><?php esc_html_e( 'E-mails des admins (optionnel)', 'sierra-logistics' ); ?></label></th>
						<td>
							<input type="file" id="admin_emails_csv" name="admin_emails_csv" accept=".csv" />
							<p class="description"><?php esc_html_e( 'CSV complémentaire (colonnes : id, email), si les e-mails ne sont pas dans admins.csv - les mots de passe Supabase ne sont pas récupérables, un lien de définition de mot de passe sera envoyé à chaque nouvel admin.', 'sierra-logistics' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Importer', 'sierra-logistics' ), 'primary', 'sierra_import_submit' ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Traite les fichiers envoyés et retourne les rapports par table.
	 *
	 * @return array<string, array{imported:int, skipped:int, errors:string[]}>
	 */
	private static function process_upload(): array {
		$reports        = array();
		$emails_csv_tmp = self::uploaded_tmp_path( 'admin_emails_csv' );

		$steps = array(
			'vehicles'  => array( Importer::class, 'import_vehicles' ),
			'quotes'    => array( Importer::class, 'import_quotes' ),
			'commandes' => array( Importer::class, 'import_commandes' ),
		);

		foreach ( $steps as $table => $callback ) {
			$tmp = self::uploaded_tmp_path( $table . '_csv' );
			if ( $tmp ) {
				$reports[ $table ] = call_user_func( $callback, $tmp );
			}
		}

		$admins_tmp = self::uploaded_tmp_path( 'admins_csv' );
		if ( $admins_tmp ) {
			$reports['admins'] = Importer::import_admins( $admins_tmp, (string) $emails_csv_tmp );
		}

		return $reports;
	}

	/**
	 * Chemin temporaire d'un fichier CSV envoyé, ou null s'il est absent.
	 *
	 * @param string $field Nom du champ de formulaire.
	 */
	private static function uploaded_tmp_path( string $field ): ?string {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce déjà vérifié par check_admin_referer() dans render(), seul appelant de process_upload() -> uploaded_tmp_path() ; is_uploaded_file() valide que le chemin vient bien d'un upload PHP, pas d'une saisie utilisateur.
		if ( empty( $_FILES[ $field ]['tmp_name'] ) || ! is_uploaded_file( $_FILES[ $field ]['tmp_name'] ) ) {
			return null;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- même chemin que ci-dessus, déjà validé par is_uploaded_file().
		return sanitize_text_field( wp_unslash( $_FILES[ $field ]['tmp_name'] ) );
	}
}
