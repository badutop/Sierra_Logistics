<?php
/**
 * Page "Véhicules" du back-office (CRUD).
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Admin;

use SierraLogistics\Pricing;
use SierraLogistics\Settings;
use SierraLogistics\Repositories\Vehicles_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gestion des véhicules : liste, ajout, modification, suppression. N'existe
 * pas dans le front actuel (les véhicules n'y sont créés que par le
 * formulaire public d'inscription, jamais modifiés), voir migration/AUDIT.md §2.2.
 */
class Vehicles_Page {

	const STATUS_LABELS = array(
		'disponible'  => 'Disponible',
		'en_course'   => 'En course',
		'maintenance' => 'En maintenance',
	);

	/**
	 * Branche les actions de formulaire (admin-post.php).
	 */
	public static function register(): void {
		add_action( 'admin_post_sierra_vehicle_save', array( __CLASS__, 'handle_save' ) );
	}

	/**
	 * Affiche la liste ou le formulaire d'ajout/modification.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'sierra_manage_vehicles' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'sierra-logistics' ) );
		}

		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- aiguillage de lecture, les actions de modification vérifient leur propre nonce.
		$id     = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'delete' === $action && $id ) {
			self::handle_delete( $id );
			return;
		}

		if ( in_array( $action, array( 'add', 'edit' ), true ) ) {
			self::render_form( 'edit' === $action ? $id : '' );
			return;
		}

		self::render_list();
	}

	/**
	 * Liste des véhicules.
	 */
	private static function render_list(): void {
		$vehicles = ( new Vehicles_Repository() )->all();
		$add_url  = admin_url( 'admin.php?page=' . Menu::SLUG_VEHICLES . '&action=add' );

		?>
		<div class="wrap">
			<h1>
				<?php esc_html_e( 'Véhicules', 'sierra-logistics' ); ?>
				<a href="<?php echo esc_url( $add_url ); ?>" class="page-title-action"><?php esc_html_e( 'Ajouter', 'sierra-logistics' ); ?></a>
			</h1>

			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Propriétaire', 'sierra-logistics' ); ?></th>
						<th><?php esc_html_e( 'Modèle', 'sierra-logistics' ); ?></th>
						<th><?php esc_html_e( 'Immatriculation', 'sierra-logistics' ); ?></th>
						<th><?php esc_html_e( 'Carburant', 'sierra-logistics' ); ?></th>
						<th><?php esc_html_e( 'Statut', 'sierra-logistics' ); ?></th>
						<th><?php esc_html_e( 'Téléphone', 'sierra-logistics' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $vehicles ) ) : ?>
						<tr><td colspan="7"><?php esc_html_e( 'Aucun véhicule enregistré.', 'sierra-logistics' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $vehicles as $vehicle ) : ?>
							<?php
							$edit_url   = admin_url( 'admin.php?page=' . Menu::SLUG_VEHICLES . '&action=edit&id=' . rawurlencode( $vehicle['id'] ) );
							$delete_url = wp_nonce_url(
								admin_url( 'admin.php?page=' . Menu::SLUG_VEHICLES . '&action=delete&id=' . rawurlencode( $vehicle['id'] ) ),
								'sierra_vehicle_delete_' . $vehicle['id']
							);
							?>
							<tr>
								<td><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $vehicle['name'] ); ?></a></td>
								<td><?php echo esc_html( $vehicle['model'] ); ?></td>
								<td><?php echo esc_html( $vehicle['license_plate'] ); ?></td>
								<td><?php echo esc_html( $vehicle['fuel_type'] ); ?></td>
								<td><?php echo esc_html( self::STATUS_LABELS[ $vehicle['status'] ] ?? $vehicle['status'] ); ?></td>
								<td><?php echo esc_html( $vehicle['contact_phone'] ); ?></td>
								<td>
									<a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Modifier', 'sierra-logistics' ); ?></a> |
									<a href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Supprimer ce véhicule ?', 'sierra-logistics' ) ); ?>');" class="sierra-link-danger">
										<?php esc_html_e( 'Supprimer', 'sierra-logistics' ); ?>
									</a>
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
	 * Formulaire d'ajout ou de modification d'un véhicule.
	 *
	 * @param string $id UUID du véhicule à modifier, vide pour une création.
	 */
	private static function render_form( string $id ): void {
		$vehicle = $id ? ( new Vehicles_Repository() )->find( $id ) : null;

		if ( $id && ! $vehicle ) {
			echo '<div class="wrap"><div class="notice notice-error"><p>' . esc_html__( 'Véhicule introuvable.', 'sierra-logistics' ) . '</p></div></div>';
			return;
		}

		$vehicle     = $vehicle ?? array(
			'name'          => '',
			'model'         => '',
			'license_plate' => '',
			'fuel_type'     => 'Diesel',
			'status'        => 'disponible',
			'contact_phone' => '',
		);
		$truck_types = array_keys( Settings::get( 'coefficients_camion', Pricing::default_coefficients() ) );

		?>
		<div class="wrap">
			<h1><?php echo $id ? esc_html__( 'Modifier le véhicule', 'sierra-logistics' ) : esc_html__( 'Ajouter un véhicule', 'sierra-logistics' ); ?></h1>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'sierra_vehicle_save_' . ( $id ? $id : 'new' ) ); ?>
				<input type="hidden" name="action" value="sierra_vehicle_save" />
				<input type="hidden" name="id" value="<?php echo esc_attr( $id ); ?>" />

				<table class="form-table">
					<tr>
						<th><label for="name"><?php esc_html_e( 'Nom du propriétaire', 'sierra-logistics' ); ?></label></th>
						<td><input type="text" id="name" name="name" class="regular-text" required value="<?php echo esc_attr( $vehicle['name'] ); ?>" /></td>
					</tr>
					<tr>
						<th><label for="model"><?php esc_html_e( 'Modèle', 'sierra-logistics' ); ?></label></th>
						<td>
							<select id="model" name="model" required>
								<?php foreach ( $truck_types as $type ) : ?>
									<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $vehicle['model'], $type ); ?>><?php echo esc_html( $type ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="license_plate"><?php esc_html_e( 'Immatriculation', 'sierra-logistics' ); ?></label></th>
						<td><input type="text" id="license_plate" name="license_plate" class="regular-text" required value="<?php echo esc_attr( $vehicle['license_plate'] ); ?>" /></td>
					</tr>
					<tr>
						<th><label for="fuel_type"><?php esc_html_e( 'Carburant', 'sierra-logistics' ); ?></label></th>
						<td>
							<select id="fuel_type" name="fuel_type">
								<option value="Diesel" <?php selected( $vehicle['fuel_type'], 'Diesel' ); ?>>Diesel</option>
								<option value="Essence" <?php selected( $vehicle['fuel_type'], 'Essence' ); ?>>Essence</option>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="status"><?php esc_html_e( 'Statut', 'sierra-logistics' ); ?></label></th>
						<td>
							<select id="status" name="status">
								<?php foreach ( self::STATUS_LABELS as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $vehicle['status'], $value ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="contact_phone"><?php esc_html_e( 'Téléphone du transporteur', 'sierra-logistics' ); ?></label></th>
						<td><input type="text" id="contact_phone" name="contact_phone" class="regular-text" value="<?php echo esc_attr( $vehicle['contact_phone'] ); ?>" /></td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Traite l'ajout ou la modification d'un véhicule.
	 */
	public static function handle_save(): void {
		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		check_admin_referer( 'sierra_vehicle_save_' . ( $id ? $id : 'new' ) );

		if ( ! current_user_can( 'sierra_manage_vehicles' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'sierra-logistics' ) );
		}

		$truck_types = array_keys( Settings::get( 'coefficients_camion', Pricing::default_coefficients() ) );
		$model       = isset( $_POST['model'] ) ? sanitize_text_field( wp_unslash( $_POST['model'] ) ) : '';
		$status      = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'disponible';

		if ( ! in_array( $model, $truck_types, true ) ) {
			wp_die( esc_html__( 'Modèle de véhicule invalide.', 'sierra-logistics' ) );
		}
		if ( ! in_array( $status, Vehicles_Repository::STATUSES, true ) ) {
			wp_die( esc_html__( 'Statut invalide.', 'sierra-logistics' ) );
		}

		$data = array(
			'name'          => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'model'         => $model,
			'license_plate' => isset( $_POST['license_plate'] ) ? sanitize_text_field( wp_unslash( $_POST['license_plate'] ) ) : '',
			'fuel_type'     => isset( $_POST['fuel_type'] ) ? sanitize_text_field( wp_unslash( $_POST['fuel_type'] ) ) : 'Diesel',
			'status'        => $status,
			'contact_phone' => isset( $_POST['contact_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_phone'] ) ) : '',
		);

		$repository = new Vehicles_Repository();
		if ( $id ) {
			$repository->update( $id, $data );
		} else {
			$repository->create( $data );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=' . Menu::SLUG_VEHICLES . '&saved=1' ) );
		exit;
	}

	/**
	 * Traite la suppression d'un véhicule.
	 *
	 * @param string $id UUID du véhicule.
	 */
	private static function handle_delete( string $id ): void {
		check_admin_referer( 'sierra_vehicle_delete_' . $id );

		( new Vehicles_Repository() )->delete( $id );

		wp_safe_redirect( admin_url( 'admin.php?page=' . Menu::SLUG_VEHICLES . '&deleted=1' ) );
		exit;
	}
}
