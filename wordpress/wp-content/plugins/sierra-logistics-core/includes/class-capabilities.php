<?php
/**
 * Rôles et capabilities du plugin.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Définit les rôles sierra_admin et sierra_agent et leurs capabilities.
 *
 * Répartition validée avec le client (voir migration/AUDIT.md et l'échange
 * de validation du plan) : un agent couvre l'activité quotidienne (devis,
 * validation de commande, facturation), un admin couvre en plus tout ce qui
 * engage l'entreprise (tarifs, flotte, comptes, import/export, suppression).
 * Le rôle WordPress natif "administrator" (webmaster technique) reçoit aussi
 * toutes les capabilities Sierra, pour ne pas avoir besoin d'un second compte
 * pour accéder au back-office métier.
 */
class Capabilities {

	const ROLE_ADMIN = 'sierra_admin';
	const ROLE_AGENT = 'sierra_agent';

	/**
	 * À incrémenter chaque fois que SHARED_CAPS ou ADMIN_ONLY_CAPS change,
	 * pour que maybe_upgrade() resynchronise les rôles sans attendre une
	 * désactivation/réactivation manuelle du plugin.
	 */
	const ROLE_VERSION        = '1.0.0';
	const OPTION_ROLE_VERSION = 'sierra_logistics_role_version';

	/**
	 * Capabilities communes à sierra_admin et sierra_agent : activité
	 * quotidienne de gestion des devis, commandes et factures.
	 */
	const SHARED_CAPS = array(
		'read',
		'sierra_view_dashboard',
		'sierra_manage_quotes',
		'sierra_validate_commande',
		'sierra_view_commandes',
		'sierra_manage_invoices',
	);

	/**
	 * Capabilities réservées à sierra_admin : tout ce qui engage l'entreprise
	 * plutôt que le traitement d'un dossier au quotidien.
	 */
	const ADMIN_ONLY_CAPS = array(
		'sierra_manage_vehicles',
		'sierra_manage_settings',
		'sierra_manage_accounts',
		'sierra_import_export',
		'sierra_delete_records',
	);

	/**
	 * Liste de toutes les capabilities définies par le plugin.
	 */
	public static function all_capabilities(): array {
		return array_merge( self::SHARED_CAPS, self::ADMIN_ONLY_CAPS );
	}

	/**
	 * Crée (ou recrée) les rôles sierra_admin et sierra_agent avec leurs
	 * capabilities, et les accorde au rôle "administrator" natif.
	 */
	public static function register_roles(): void {
		// Idempotent : on repart d'un rôle propre à chaque activation ou mise
		// à jour, pour que la liste de capabilities reste toujours exacte
		// même si une version précédente du plugin en définissait d'autres.
		remove_role( self::ROLE_AGENT );
		remove_role( self::ROLE_ADMIN );

		add_role(
			self::ROLE_AGENT,
			__( 'Agent Sierra Logistics', 'sierra-logistics' ),
			array_fill_keys( self::SHARED_CAPS, true )
		);

		add_role(
			self::ROLE_ADMIN,
			__( 'Admin Sierra Logistics', 'sierra-logistics' ),
			array_fill_keys( self::all_capabilities(), true )
		);

		self::grant_to_administrator();

		update_option( self::OPTION_ROLE_VERSION, self::ROLE_VERSION );
	}

	/**
	 * Rejoue register_roles() si la définition des capabilities a changé
	 * depuis la dernière exécution, sans attendre une désactivation/
	 * réactivation manuelle du plugin.
	 */
	public static function maybe_upgrade(): void {
		if ( get_option( self::OPTION_ROLE_VERSION ) !== self::ROLE_VERSION ) {
			self::register_roles();
		}
	}

	/**
	 * Supprime les rôles sierra_admin et sierra_agent, et retire leurs
	 * capabilities du rôle "administrator". Les utilisateurs ayant un de ces
	 * rôles perdent leur accès au plugin, mais pas leur compte WordPress.
	 */
	public static function remove_roles(): void {
		remove_role( self::ROLE_AGENT );
		remove_role( self::ROLE_ADMIN );
		self::revoke_from_administrator();
	}

	/**
	 * Accorde toutes les capabilities Sierra au rôle "administrator" natif.
	 */
	public static function grant_to_administrator(): void {
		$role = get_role( 'administrator' );
		if ( ! $role ) {
			return;
		}
		foreach ( self::all_capabilities() as $cap ) {
			$role->add_cap( $cap );
		}
	}

	/**
	 * Retire les capabilities Sierra du rôle "administrator" natif.
	 */
	public static function revoke_from_administrator(): void {
		$role = get_role( 'administrator' );
		if ( ! $role ) {
			return;
		}
		foreach ( self::all_capabilities() as $cap ) {
			$role->remove_cap( $cap );
		}
	}
}
