<?php
/**
 * Commande WP-CLI d'import des données Supabase.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * `wp sierra import <table> <fichier.csv> [--emails=<fichier.csv>]`
 *
 * Équivalent en ligne de commande de la page d'admin "Import" (Admin\Import_Page) :
 * même classe Importer, donc même comportement idempotent. À exécuter dans
 * l'ordre vehicles, quotes, commandes, admins.
 */
class Import_Command {

	/**
	 * Importe un CSV exporté depuis Supabase dans une table du plugin.
	 *
	 * ## OPTIONS
	 *
	 * <table>
	 * : Table à importer (vehicles, quotes, commandes ou admins).
	 *
	 * <file>
	 * : Chemin du fichier CSV à importer.
	 *
	 * [--emails=<file>]
	 * : Pour `admins` uniquement : CSV complémentaire (colonnes id,email) si admins.csv n'a pas d'email.
	 *
	 * ## EXAMPLES
	 *
	 *     wp sierra import vehicles ./export/vehicles.csv
	 *     wp sierra import quotes ./export/quotes.csv
	 *     wp sierra import commandes ./export/commandes.csv
	 *     wp sierra import admins ./export/admins.csv --emails=./export/admin-emails.csv
	 *
	 * @param string[]             $args       Arguments positionnels (table, fichier).
	 * @param array<string,string> $assoc_args Arguments nommés (emails).
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		list( $table, $path ) = $args;

		if ( ! file_exists( $path ) ) {
			\WP_CLI::error( "Fichier introuvable : {$path}" );
			return;
		}

		switch ( $table ) {
			case 'vehicles':
				$report = Importer::import_vehicles( $path );
				break;
			case 'quotes':
				$report = Importer::import_quotes( $path );
				break;
			case 'commandes':
				$report = Importer::import_commandes( $path );
				break;
			case 'admins':
				$report = Importer::import_admins( $path, $assoc_args['emails'] ?? '' );
				break;
			default:
				\WP_CLI::error( "Table inconnue : {$table} (attendu : vehicles, quotes, commandes, admins)" );
				return;
		}

		foreach ( $report['errors'] as $error ) {
			\WP_CLI::warning( $error );
		}

		\WP_CLI::success(
			sprintf( '%s : %d importées, %d ignorées.', $table, $report['imported'], $report['skipped'] )
		);
	}
}

\WP_CLI::add_command( 'sierra import', Import_Command::class );
