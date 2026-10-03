<?php
/**
 * Plugin Name:       Sierra Logistics Core
 * Description:       Devis, commandes, véhicules et facturation pour Sierra Logistics.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Sierra Logistics
 * Text Domain:       sierra-logistics
 * Domain Path:       /languages
 *
 * @package SierraLogistics
 */

namespace SierraLogistics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SIERRA_LOGISTICS_VERSION', '0.1.0' );
define( 'SIERRA_LOGISTICS_DB_VERSION', '1.2.0' );
define( 'SIERRA_LOGISTICS_PLUGIN_FILE', __FILE__ );
define( 'SIERRA_LOGISTICS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SIERRA_LOGISTICS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/class-schema.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/class-capabilities.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/class-settings.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/class-activator.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/class-deactivator.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/class-plugin.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/data/class-villes-senegal.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/class-pricing.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/repositories/class-vehicles-repository.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/repositories/class-quotes-repository.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/repositories/class-commandes-repository.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/repositories/class-quote-history-repository.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/class-formatting.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/class-invoice-numbering.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/class-invoice-pdf.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/class-commande-validator.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/class-importer.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/class-import-command.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/rest/class-cors.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/rest/class-rate-limiter.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/rest/class-quotes-controller.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/rest/class-commandes-controller.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/rest/class-vehicles-controller.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/rest/class-reference-controller.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/rest/class-bootstrap.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/admin/class-menu.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/admin/class-dashboard-page.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/admin/class-quotes-list-table.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/admin/class-quotes-page.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/admin/class-commandes-page.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/admin/class-vehicles-page.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/admin/class-settings-page.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/admin/class-import-page.php';
require_once SIERRA_LOGISTICS_PLUGIN_DIR . 'includes/admin/class-bootstrap.php';

register_activation_hook( __FILE__, array( Activator::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( Deactivator::class, 'deactivate' ) );

add_action( 'plugins_loaded', array( Plugin::class, 'init' ) );
