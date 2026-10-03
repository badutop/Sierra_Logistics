<?php
/**
 * Gabarit de secours. N'est jamais vraiment affiché : toute visite
 * front-end est redirigée vers la racine du site par
 * sierra_gestion_redirect_frontend() dans functions.php, avant que WordPress
 * n'ait besoin de charger un gabarit de thème.
 *
 * @package SierraGestion
 */

wp_safe_redirect( home_url( '/' ), 301 );
exit;
