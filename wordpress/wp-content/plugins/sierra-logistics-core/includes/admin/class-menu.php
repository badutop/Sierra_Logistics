<?php
/**
 * Menu d'administration du plugin.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Admin;

use SierraLogistics\Capabilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre le menu "Sierra Logistics" et ses sous-pages, et restreint
 * l'espace d'administration des comptes sierra_admin/sierra_agent (qui
 * n'ont pas manage_options) au seul périmètre du plugin : menus WordPress
 * inutiles masqués, connexion redirigée vers le tableau de bord Sierra.
 */
class Menu {

	const SLUG_DASHBOARD = 'sierra-logistics';
	const SLUG_QUOTES    = 'sierra-quotes';
	const SLUG_COMMANDES = 'sierra-commandes';
	const SLUG_VEHICLES  = 'sierra-vehicles';
	const SLUG_SETTINGS  = 'sierra-settings';
	const SLUG_IMPORT    = 'sierra-import';

	/**
	 * Branche les hooks d'administration du plugin.
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_menu', array( __CLASS__, 'hide_unrelated_menus' ), 999 );
		add_action( 'admin_bar_menu', array( __CLASS__, 'hide_admin_bar_nodes' ), 999 );
		add_filter( 'login_redirect', array( __CLASS__, 'redirect_after_login' ), 10, 3 );
		add_action( 'admin_init', array( __CLASS__, 'redirect_from_wp_dashboard' ) );
	}

	/**
	 * Enregistre le menu "Sierra Logistics" et ses sous-pages.
	 */
	public static function register_menu(): void {
		if ( ! current_user_can( 'sierra_view_dashboard' ) ) {
			return;
		}

		add_menu_page(
			__( 'Sierra Logistics', 'sierra-logistics' ),
			__( 'Sierra Logistics', 'sierra-logistics' ),
			'sierra_view_dashboard',
			self::SLUG_DASHBOARD,
			array( Dashboard_Page::class, 'render' ),
			'dashicons-truck',
			3
		);

		add_submenu_page(
			self::SLUG_DASHBOARD,
			__( 'Tableau de bord', 'sierra-logistics' ),
			__( 'Tableau de bord', 'sierra-logistics' ),
			'sierra_view_dashboard',
			self::SLUG_DASHBOARD,
			array( Dashboard_Page::class, 'render' )
		);

		add_submenu_page(
			self::SLUG_DASHBOARD,
			__( 'Devis', 'sierra-logistics' ),
			__( 'Devis', 'sierra-logistics' ),
			'sierra_manage_quotes',
			self::SLUG_QUOTES,
			array( Quotes_Page::class, 'render' )
		);

		add_submenu_page(
			self::SLUG_DASHBOARD,
			__( 'Commandes', 'sierra-logistics' ),
			__( 'Commandes', 'sierra-logistics' ),
			'sierra_view_commandes',
			self::SLUG_COMMANDES,
			array( Commandes_Page::class, 'render' )
		);

		add_submenu_page(
			self::SLUG_DASHBOARD,
			__( 'Véhicules', 'sierra-logistics' ),
			__( 'Véhicules', 'sierra-logistics' ),
			'sierra_manage_vehicles',
			self::SLUG_VEHICLES,
			array( Vehicles_Page::class, 'render' )
		);

		add_submenu_page(
			self::SLUG_DASHBOARD,
			__( 'Réglages', 'sierra-logistics' ),
			__( 'Réglages', 'sierra-logistics' ),
			'sierra_manage_settings',
			self::SLUG_SETTINGS,
			array( Settings_Page::class, 'render' )
		);

		add_submenu_page(
			self::SLUG_DASHBOARD,
			__( 'Import', 'sierra-logistics' ),
			__( 'Import', 'sierra-logistics' ),
			'sierra_import_export',
			self::SLUG_IMPORT,
			array( Import_Page::class, 'render' )
		);
	}

	/**
	 * Masque les menus WordPress natifs pour les comptes métier (sierra_admin,
	 * sierra_agent) qui n'ont pas manage_options, càd qui ne sont pas aussi
	 * de vrais administrateurs WordPress techniques.
	 */
	public static function hide_unrelated_menus(): void {
		if ( ! self::is_business_only_user() ) {
			return;
		}

		global $menu;
		if ( ! is_array( $menu ) ) {
			return;
		}

		foreach ( $menu as $position => $item ) {
			$slug = $item[2] ?? '';
			if ( 0 !== strpos( $slug, 'sierra-' ) && 'profile.php' !== $slug ) {
				unset( $menu[ $position ] );
			}
		}
	}

	/**
	 * Masque les éléments non pertinents de la barre d'administration
	 * (commentaires, "Nouveau", logo WordPress) pour les comptes métier.
	 *
	 * @param \WP_Admin_Bar $admin_bar Barre d'administration en cours de rendu.
	 */
	public static function hide_admin_bar_nodes( \WP_Admin_Bar $admin_bar ): void {
		if ( ! self::is_business_only_user() ) {
			return;
		}

		foreach ( array( 'wp-logo', 'comments', 'new-content' ) as $node_id ) {
			$admin_bar->remove_node( $node_id );
		}
	}

	/**
	 * Redirige un compte métier connecté vers le tableau de bord Sierra
	 * plutôt que vers le tableau de bord WordPress.
	 *
	 * @param string             $redirect_to           URL de redirection demandée.
	 * @param string             $requested_redirect_to URL de redirection initialement demandée.
	 * @param \WP_User|\WP_Error $user                  Utilisateur qui vient de se connecter.
	 */
	public static function redirect_after_login( $redirect_to, $requested_redirect_to, $user ) {
		if ( ! ( $user instanceof \WP_User ) ) {
			return $redirect_to;
		}

		if ( $user->has_cap( 'sierra_view_dashboard' ) && ! $user->has_cap( 'manage_options' ) ) {
			return admin_url( 'admin.php?page=' . self::SLUG_DASHBOARD );
		}

		return $redirect_to;
	}

	/**
	 * Redirige un compte métier qui atterrit sur le tableau de bord WordPress
	 * (lien direct, favori) vers le tableau de bord Sierra.
	 */
	public static function redirect_from_wp_dashboard(): void {
		global $pagenow;

		if ( 'index.php' === $pagenow && self::is_business_only_user() ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . self::SLUG_DASHBOARD ) );
			exit;
		}
	}

	/**
	 * Vrai pour un utilisateur connecté qui a accès au plugin mais n'est pas
	 * un administrateur WordPress technique.
	 */
	private static function is_business_only_user(): bool {
		return current_user_can( 'sierra_view_dashboard' ) && ! current_user_can( 'manage_options' );
	}
}
