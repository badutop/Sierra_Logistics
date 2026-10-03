<?php
/**
 * Fonctions du thème Sierra Gestion.
 *
 * @package SierraGestion
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URL du front public. Jamais home_url() : WP_HOME doit être égal à
 * WP_SITEURL (voir wp-config-local.php) pour que le routage interne de
 * WordPress (API REST, permaliens sous /gestion/) fonctionne - sinon
 * WordPress ne reconnaît jamais son propre sous-dossier d'installation et
 * redirige toute requête vers la racine (constaté en production). La
 * constante SIERRA_FRONT_URL donne donc l'URL du front séparément.
 *
 * @param string $path Chemin relatif à ajouter.
 */
function sierra_gestion_front_url( string $path = '' ): string {
	$base = defined( 'SIERRA_FRONT_URL' ) ? SIERRA_FRONT_URL : home_url();
	return rtrim( $base, '/' ) . '/' . ltrim( $path, '/' );
}

/**
 * Redirige (301) toute visite front-end WordPress vers la racine du site
 * (le front Next.js). Ce thème ne sert que de support à wp-admin : il n'a
 * aucun rendu public à lui, par design (voir migration/AUDIT.md -
 * architecture "WordPress dans /gestion/, site public ailleurs").
 *
 * `template_redirect` ne se déclenche que pour les requêtes qui chargeraient
 * normalement un gabarit de thème : wp-admin, wp-login.php et l'API REST
 * s'arrêtent avant ce hook et ne sont donc jamais concernés.
 */
function sierra_gestion_redirect_frontend(): void {
	wp_safe_redirect( sierra_gestion_front_url(), 301 );
	exit;
}
add_action( 'template_redirect', 'sierra_gestion_redirect_frontend' );

/**
 * Charge le CSS de personnalisation de l'écran de connexion. L'URL du logo
 * est injectée en ligne (pas dans le .css statique) car elle dépend du
 * domaine, jamais codée en dur (voir migration/DEPLOIEMENT.md).
 */
function sierra_gestion_login_styles(): void {
	wp_enqueue_style(
		'sierra-gestion-login',
		get_stylesheet_directory_uri() . '/login.css',
		array( 'login' ),
		wp_get_theme()->get( 'Version' )
	);

	$logo_url = esc_url( sierra_gestion_front_url( '/images/sierra-logistics-logo-header.png' ) );
	wp_add_inline_style(
		'sierra-gestion-login',
		".login h1 a { background-image: url('{$logo_url}'); }"
	);
}
add_action( 'login_enqueue_scripts', 'sierra_gestion_login_styles' );

/**
 * Logo de l'écran de connexion : le logo Sierra Logistics servi par le front.
 */
function sierra_gestion_login_logo_url(): string {
	return sierra_gestion_front_url();
}
add_filter( 'login_headerurl', 'sierra_gestion_login_logo_url' );

/**
 * Texte alternatif du logo de l'écran de connexion.
 */
function sierra_gestion_login_logo_text(): string {
	return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'sierra_gestion_login_logo_text' );
