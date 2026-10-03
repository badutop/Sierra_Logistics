<?php
/**
 * wp-config-local.php pour l'environnement Docker local.
 *
 * Committé, contrairement à son équivalent de production
 * (wordpress/wp-config-local.php.example) : il ne contient aucun secret,
 * seulement des appels à getenv() lisant les variables injectées par
 * docker-compose depuis docker/.env (voir docker/.env.example).
 *
 * @package SierraGestion
 */

define( 'DB_NAME', getenv( 'DB_NAME' ) ?: 'wordpress' );
define( 'DB_USER', getenv( 'DB_USER' ) ?: 'wordpress' );
define( 'DB_PASSWORD', getenv( 'DB_PASSWORD' ) ?: 'wordpress' );
define( 'DB_HOST', getenv( 'DB_HOST' ) ?: 'db' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

// WP_HOME doit être égal à WP_SITEURL : WordPress dérive de WP_HOME le
// préfixe à retirer d'une requête entrante pour reconnaître ses propres
// routes (API REST, permaliens). S'ils diffèrent et que WP vit dans un
// sous-dossier, WordPress ne reconnaît jamais ce sous-dossier et redirige
// tout vers la racine (constaté en production, voir migration/AUDIT.md).
// SIERRA_FRONT_URL donne séparément l'URL du front, utilisée par le plugin
// et le thème à la place de home_url() pour tout lien vers le site public.
define( 'WP_HOME', getenv( 'WP_SITEURL' ) ?: 'http://localhost:8080/gestion' );
define( 'WP_SITEURL', getenv( 'WP_SITEURL' ) ?: 'http://localhost:8080/gestion' );
define( 'SIERRA_FRONT_URL', getenv( 'WP_HOME' ) ?: 'http://localhost:8080' );
define( 'WP_ENVIRONMENT_TYPE', getenv( 'WP_ENVIRONMENT_TYPE' ) ?: 'local' );

// Clés de sécurité : valeurs fixes, sans enjeu sur un environnement de
// développement jetable. Ne jamais réutiliser en production, voir
// wordpress/wp-config-local.php.example (générateur officiel de clés).
define( 'AUTH_KEY', 'local-dev-only' );
define( 'SECURE_AUTH_KEY', 'local-dev-only' );
define( 'LOGGED_IN_KEY', 'local-dev-only' );
define( 'NONCE_KEY', 'local-dev-only' );
define( 'AUTH_SALT', 'local-dev-only' );
define( 'SECURE_AUTH_SALT', 'local-dev-only' );
define( 'LOGGED_IN_SALT', 'local-dev-only' );
define( 'NONCE_SALT', 'local-dev-only' );

$table_prefix = 'wp_';

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
