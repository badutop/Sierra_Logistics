<?php
/**
 * Accès à l'historique des devis.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics\Repositories;

use SierraLogistics\Schema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accès à wp_sierra_quote_history : journal des changements apportés à un
 * devis depuis le back-office (changement de statut, recalcul, validation).
 */
class Quote_History_Repository {

	/**
	 * Ajoute une entrée d'historique pour un devis.
	 *
	 * @param string   $quote_id Devis concerné.
	 * @param string   $message  Description de l'action (déjà en français, prête à afficher).
	 * @param int|null $user_id  Auteur de l'action, ou null (ex. action automatique).
	 */
	public function log( string $quote_id, string $message, ?int $user_id = null ): void {
		global $wpdb;
		$table = Schema::quote_history_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- table personnalisée du plugin, $wpdb->insert() échappe déjà les valeurs.
		$wpdb->insert(
			$table,
			array(
				'quote_id'   => $quote_id,
				'created_at' => gmdate( 'Y-m-d H:i:s' ),
				'user_id'    => $user_id,
				'message'    => $message,
			)
		);
	}

	/**
	 * Historique d'un devis, le plus récent en premier.
	 *
	 * @param string $quote_id Devis concerné.
	 */
	public function for_quote( string $quote_id ): array {
		global $wpdb;
		$table = Schema::quote_history_table();
		$sql   = "SELECT * FROM {$table} WHERE quote_id = %s ORDER BY created_at DESC, id DESC";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- {$table} est un nom de table interne, jamais une donnée utilisateur ; $sql est une chaîne littérale assignée juste au-dessus.
		return $wpdb->get_results( $wpdb->prepare( $sql, $quote_id ), ARRAY_A );
	}
}
