<?php
/**
 * Génération des factures PDF (Dompdf).
 *
 * @package SierraLogistics
 */

namespace SierraLogistics;

use Dompdf\Dompdf;
use Dompdf\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rendu PDF d'une facture proforma ou définitive. Nouveauté par rapport à
 * l'existant (voir migration/AUDIT.md §5 : la "facture" était jusqu'ici une
 * simple page HTML imprimable, sans PDF généré côté serveur).
 */
class Invoice_Pdf {

	/**
	 * Génère le PDF et retourne son contenu binaire.
	 *
	 * @param array      $quote    Devis (ligne wp_sierra_quotes).
	 * @param array|null $commande Commande associée, si le devis est validé (facture définitive).
	 */
	public static function render( array $quote, ?array $commande = null ): string {
		self::require_dompdf();

		$options = new Options();
		$options->set( 'isRemoteEnabled', false );
		$options->set( 'defaultFont', 'DejaVu Sans' );

		$dompdf = new Dompdf( $options );
		$dompdf->loadHtml( self::html( $quote, $commande ), 'UTF-8' );
		$dompdf->setPaper( 'A4', 'portrait' );
		$dompdf->render();

		return $dompdf->output();
	}

	/**
	 * Nom de fichier suggéré pour le téléchargement.
	 *
	 * @param array      $quote    Devis.
	 * @param array|null $commande Commande associée, si la facture est définitive.
	 */
	public static function filename( array $quote, ?array $commande = null ): string {
		$number = $commande ? $commande['invoice_number'] : $quote['invoice_number'];
		return sanitize_file_name( ( $number ? $number : $quote['id'] ) . '.pdf' );
	}

	/**
	 * Génère le PDF et l'écrit dans un fichier temporaire, pour une pièce
	 * jointe d'e-mail. Le dossier est protégé par un .htaccess ("deny from
	 * all") car wp-content/uploads est servi publiquement par défaut et ces
	 * fichiers contiennent des données personnelles de clients ; appelant
	 * responsable de supprimer le fichier une fois l'envoi terminé.
	 *
	 * @param array      $quote    Devis.
	 * @param array|null $commande Commande associée, si la facture est définitive.
	 *
	 * @return string Chemin absolu du fichier PDF temporaire.
	 */
	public static function write_temp_file( array $quote, ?array $commande = null ): string {
		$pdf        = self::render( $quote, $commande );
		$upload_dir = wp_upload_dir();
		$tmp_dir    = trailingslashit( $upload_dir['basedir'] ) . 'sierra-tmp';

		wp_mkdir_p( $tmp_dir );

		$htaccess = trailingslashit( $tmp_dir ) . '.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			self::filesystem()->put_contents( $htaccess, "Deny from all\n", FS_CHMOD_FILE );
		}

		$path = trailingslashit( $tmp_dir ) . wp_unique_filename( $tmp_dir, self::filename( $quote, $commande ) );
		self::filesystem()->put_contents( $path, $pdf, FS_CHMOD_FILE );

		return $path;
	}

	/**
	 * Accès à WP_Filesystem, initialisé à la demande.
	 */
	private static function filesystem(): \WP_Filesystem_Base {
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		return $wp_filesystem;
	}

	/**
	 * Charge l'autoloader Composer de Dompdf. Message clair plutôt qu'une
	 * erreur fatale PHP si `composer install` n'a pas été exécuté (voir
	 * migration/DEPLOIEMENT.md : Dompdf est une dépendance de production,
	 * embarquée dans le plugin au déploiement).
	 *
	 * @throws \RuntimeException Si le dossier vendor/ de Dompdf est absent.
	 */
	private static function require_dompdf(): void {
		if ( class_exists( Dompdf::class ) ) {
			return;
		}

		$autoload = SIERRA_LOGISTICS_PLUGIN_DIR . 'vendor/autoload.php';
		if ( ! file_exists( $autoload ) ) {
			throw new \RuntimeException(
				'Dompdf introuvable : exécutez "composer install" dans le dossier du plugin (voir migration/DEPLOIEMENT.md).'
			);
		}

		require_once $autoload;
	}

	/**
	 * Construit le HTML de la facture. Toutes les valeurs dynamiques sont
	 * échappées (esc_html) : certaines viennent du formulaire public de
	 * demande de devis, jamais sûres à injecter telles quelles dans le HTML
	 * rendu par Dompdf.
	 *
	 * @param array      $quote    Devis.
	 * @param array|null $commande Commande associée, si la facture est définitive.
	 */
	private static function html( array $quote, ?array $commande ): string {
		$settings      = Settings::all();
		$is_definitive = (bool) $commande;
		$number        = $is_definitive ? $commande['invoice_number'] : $quote['invoice_number'];
		$title         = $is_definitive ? 'FACTURE DÉFINITIVE' : 'FACTURE PROFORMA';
		$badge_color   = $is_definitive ? '#00a32a' : '#dba617';
		$date          = Formatting::to_dakar_time( $quote['created_at'], 'd/m/Y' );

		ob_start();
		?>
		<html>
		<head>
			<meta charset="UTF-8" />
			<style>
				body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1a1a1a; }
				h1 { font-size: 18px; margin: 0; color: #111111; }
				table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
				th, td { padding: 6px 8px; text-align: left; }
				.header { border-bottom: 2px solid #111111; padding-bottom: 12px; margin-bottom: 16px; overflow: hidden; }
				.header .left { float: left; }
				.header .right { float: right; text-align: right; }
				.badge { display: inline-block; padding: 3px 10px; border-radius: 3px; color: #fff; font-weight: bold; font-size: 11px; }
				.section-title { background: #111111; color: #fff; padding: 6px 10px; font-weight: bold; margin-bottom: 8px; }
				.data-table th { background: #f0f0f0; }
				.data-table, .data-table th, .data-table td { border: 1px solid #ddd; }
				.totals td { border-bottom: 1px solid #eee; }
				.totals .total-row td { font-weight: bold; font-size: 14px; background: #f0f0f0; }
				.footer { margin-top: 24px; font-size: 10px; color: #666; }
			</style>
		</head>
		<body>
			<div class="header">
				<div class="left">
					<h1><?php echo esc_html( $settings['company_name'] ); ?></h1>
					<p><?php echo esc_html( $title ); ?></p>
					<p><strong>N&deg;:</strong> <?php echo esc_html( $number ? $number : '-' ); ?></p>
					<p><strong>Date :</strong> <?php echo esc_html( $date ); ?></p>
					<span class="badge" style="background:<?php echo esc_attr( $badge_color ); ?>">
						<?php echo esc_html( $is_definitive ? 'DÉFINITIVE' : 'PROFORMA' ); ?>
					</span>
				</div>
				<div class="right">
					<p><?php echo esc_html( $settings['company_address'] ); ?></p>
					<p><?php echo esc_html( $settings['company_phone'] ); ?></p>
					<?php if ( $settings['company_ninea'] ) : ?>
						<p>NINEA : <?php echo esc_html( $settings['company_ninea'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<div class="section-title">INFORMATIONS CLIENT</div>
			<table>
				<tr>
					<td><strong>Nom :</strong> <?php echo esc_html( $quote['nom'] ? $quote['nom'] : '-' ); ?></td>
					<td><strong>Téléphone :</strong> <?php echo esc_html( $quote['telephone'] ? $quote['telephone'] : '-' ); ?></td>
				</tr>
				<tr>
					<td><strong>Email :</strong> <?php echo esc_html( $quote['email'] ? $quote['email'] : '-' ); ?></td>
					<td><strong>Date d'expédition :</strong> <?php echo esc_html( $quote['date_expedition'] ? $quote['date_expedition'] : '-' ); ?></td>
				</tr>
			</table>

			<div class="section-title">DÉTAILS DE L'EXPÉDITION</div>
			<table class="data-table">
				<tr>
					<th>Ville départ</th><th>Ville arrivée</th><th>Km</th><th>Marchandise</th><th>Poids</th><th>Camion</th>
				</tr>
				<tr>
					<td><?php echo esc_html( $quote['ville_depart'] ); ?></td>
					<td><?php echo esc_html( $quote['ville_arrivee'] ); ?></td>
					<td><?php echo esc_html( $quote['distance'] ? $quote['distance'] . ' km' : '-' ); ?></td>
					<td><?php echo esc_html( $quote['type_marchandise'] ); ?></td>
					<td><?php echo esc_html( $quote['poids'] ? $quote['poids'] . ' kg' : '-' ); ?></td>
					<td><?php echo esc_html( $quote['type_vehicle'] ); ?></td>
				</tr>
			</table>

			<div class="section-title">INFORMATIONS TARIFAIRES</div>
			<table class="totals">
				<tr><td>Transport base</td><td style="text-align:right;"><?php echo esc_html( Formatting::fcfa( $quote['montant_transport'] ) ); ?></td></tr>
				<?php if ( $quote['majoration'] > 0 ) : ?>
					<tr><td>Majoration</td><td style="text-align:right;"><?php echo esc_html( Formatting::fcfa( $quote['majoration'] ) ); ?></td></tr>
				<?php endif; ?>
				<tr><td>Sous-total</td><td style="text-align:right;"><?php echo esc_html( Formatting::fcfa( $quote['sous_total'] ) ); ?></td></tr>
				<tr><td>TVA</td><td style="text-align:right;"><?php echo esc_html( Formatting::fcfa( $quote['tva'] ) ); ?></td></tr>
				<tr class="total-row"><td>TOTAL</td><td style="text-align:right;"><?php echo esc_html( Formatting::fcfa( $quote['total'] ) ); ?></td></tr>
			</table>

			<?php if ( $is_definitive ) : ?>
				<div class="section-title">EXÉCUTION DE LA COMMANDE</div>
				<table>
					<tr>
						<td><strong>Camion affecté :</strong> <?php echo esc_html( $commande['camion_immatriculation'] ); ?></td>
						<td><strong>Chauffeur :</strong> <?php echo esc_html( $commande['chauffeur'] ); ?></td>
					</tr>
					<tr>
						<td><strong>Téléphone chauffeur :</strong> <?php echo esc_html( $commande['telephone_chauffeur'] ? $commande['telephone_chauffeur'] : '-' ); ?></td>
						<td><strong>Date validation :</strong> <?php echo esc_html( Formatting::to_dakar_time( $commande['date_validation'], 'd/m/Y' ) ); ?></td>
					</tr>
				</table>
			<?php endif; ?>

			<div class="footer">
				<?php echo esc_html( $settings['company_name'] . ' — ' . $settings['company_address'] . ' — ' . $settings['company_phone'] ); ?>
			</div>
		</body>
		</html>
		<?php
		return (string) ob_get_clean();
	}
}
