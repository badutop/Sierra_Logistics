<?php
/**
 * Configuration WordPress.
 *
 * Toutes les valeurs sensibles ou dépendantes de l'environnement (base de
 * données, clés de sécurité, WP_HOME/WP_SITEURL, WP_ENVIRONMENT_TYPE) vivent
 * dans wp-config-local.php, jamais commité en production (voir
 * migration/DEPLOIEMENT.md et wp-config-local.php.example ci-contre). En
 * local, docker/wp-config-local.php (committé, sans secret) lit ces mêmes
 * valeurs depuis les variables d'environnement injectées par
 * docker-compose (voir docker/.env.example).
 *
 * @package SierraGestion
 */

require __DIR__ . '/wp-config-local.php';

if ( ! isset( $table_prefix ) ) {
	$table_prefix = 'wp_';
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
