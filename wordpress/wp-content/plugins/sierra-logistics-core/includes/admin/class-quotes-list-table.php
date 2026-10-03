<?php
/**
 * Liste des devis (WP_List_Table).
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Admin;

use SierraLogistics\Formatting;
use SierraLogistics\Repositories\Quotes_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Liste des devis avec filtres (statut, dates, recherche), triée par date de
 * création décroissante. Port de src/app/admin/(dashboard)/factures/page.jsx.
 */
class Quotes_List_Table extends \WP_List_Table {

	const PER_PAGE = 20;

	/**
	 * Constructeur.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'devis',
				'plural'   => 'devis',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Colonnes affichées.
	 */
	public function get_columns(): array {
		return array(
			'nom'        => __( 'Client', 'sierra-logistics' ),
			'trajet'     => __( 'Trajet', 'sierra-logistics' ),
			'total'      => __( 'Montant', 'sierra-logistics' ),
			'statut'     => __( 'Statut', 'sierra-logistics' ),
			'created_at' => __( 'Date', 'sierra-logistics' ),
		);
	}

	/**
	 * Filtres et pagination, à partir des paramètres de la requête.
	 */
	public function prepare_items(): void {
		$repository = new Quotes_Repository();

		$args = array(
			'statut'    => isset( $_GET['statut'] ) ? sanitize_text_field( wp_unslash( $_GET['statut'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- filtre de lecture (GET), pas une action qui modifie des données.
			'search'    => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'date_from' => isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'date_to'   => isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		);

		$current_page = $this->get_pagenum();
		$total_items  = $repository->count( $args );

		$args['limit']  = self::PER_PAGE;
		$args['offset'] = ( $current_page - 1 ) * self::PER_PAGE;

		$this->items = $repository->all( $args );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => self::PER_PAGE,
				'total_pages' => (int) ceil( $total_items / self::PER_PAGE ),
			)
		);

		$this->_column_headers = array( $this->get_columns(), array(), array() );
	}

	/**
	 * Rendu par défaut d'une colonne.
	 *
	 * @param array  $item        Ligne en cours.
	 * @param string $column_name Nom de la colonne.
	 */
	public function column_default( $item, $column_name ): string {
		switch ( $column_name ) {
			case 'trajet':
				return esc_html( $item['ville_depart'] . ' → ' . $item['ville_arrivee'] );
			case 'total':
				return esc_html( Formatting::fcfa( $item['total'] ) );
			case 'statut':
				return 'commandé' === $item['statut']
					? '<span class="sierra-badge sierra-badge--success">' . esc_html__( 'Commandé', 'sierra-logistics' ) . '</span>'
					: '<span class="sierra-badge sierra-badge--warning">' . esc_html__( 'En attente', 'sierra-logistics' ) . '</span>';
			case 'created_at':
				return esc_html( Formatting::to_dakar_time( $item['created_at'], 'd/m/Y H:i' ) );
			default:
				return '';
		}
	}

	/**
	 * Rendu de la colonne "Client" avec les actions de ligne (voir, supprimer).
	 *
	 * @param array $item Ligne en cours.
	 */
	public function column_nom( $item ): string {
		$view_url = Quotes_Page::detail_url( $item['id'] );

		$actions = array(
			'view' => sprintf( '<a href="%s">%s</a>', esc_url( $view_url ), esc_html__( 'Voir', 'sierra-logistics' ) ),
		);

		if ( current_user_can( 'sierra_delete_records' ) ) {
			$delete_url        = Quotes_Page::action_url( 'delete', $item['id'] );
			$actions['delete'] = sprintf(
				'<a href="%s" onclick="return confirm(\'%s\');" class="sierra-link-danger">%s</a>',
				esc_url( $delete_url ),
				esc_js( __( 'Supprimer ce devis ?', 'sierra-logistics' ) ),
				esc_html__( 'Supprimer', 'sierra-logistics' )
			);
		}

		return sprintf(
			'<strong><a href="%s">%s</a></strong><br><span class="description">%s</span>%s',
			esc_url( $view_url ),
			esc_html( $item['nom'] ? $item['nom'] : __( 'Client', 'sierra-logistics' ) ),
			esc_html( $item['telephone'] ),
			$this->row_actions( $actions )
		);
	}

	/**
	 * Aucun devis trouvé.
	 */
	public function no_items(): void {
		esc_html_e( 'Aucun devis pour le moment.', 'sierra-logistics' );
	}
}
