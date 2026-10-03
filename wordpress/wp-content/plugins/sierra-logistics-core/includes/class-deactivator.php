<?php
/**
 * Désactivation du plugin.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tâches exécutées à la désactivation du plugin.
 */
class Deactivator {

	/**
	 * Ne supprime ni tables ni options : une désactivation est réversible par
	 * nature. La perte de données n'a lieu que via uninstall.php, déclenché
	 * uniquement par une suppression explicite du plugin depuis wp-admin.
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
