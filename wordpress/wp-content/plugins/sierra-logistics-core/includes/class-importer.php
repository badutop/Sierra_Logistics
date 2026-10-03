<?php
/**
 * Import des données exportées depuis Supabase.
 *
 * @package SierraLogistics
 */

namespace SierraLogistics;

use SierraLogistics\Repositories\Commandes_Repository;
use SierraLogistics\Repositories\Quotes_Repository;
use SierraLogistics\Repositories\Vehicles_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Importe les CSV exportés depuis le projet Supabase dans les tables du
 * plugin, dans l'ordre vehicles -> quotes -> commandes -> admins (les
 * commandes référencent un devis et un véhicule, les devis n'ont pas de
 * dépendance). Idempotent : chaque ligne est identifiée par son UUID
 * d'origine (conservé tel quel), relancer l'import sur le même export met
 * juste à jour les mêmes lignes sans créer de doublon.
 *
 * Les comptes "admins" ne deviennent pas des lignes de table mais des
 * wp_users natifs, avec un lien conservé en usermeta
 * (self::META_LEGACY_ADMIN_ID) - voir migration/sierra_schema_mysql.sql pour
 * le détail de cette décision.
 */
class Importer {

	const META_LEGACY_ADMIN_ID = 'sierra_legacy_admin_id';

	/**
	 * Importe le CSV des véhicules.
	 *
	 * @param string $csv_path Chemin du fichier CSV.
	 *
	 * @return array{imported:int, skipped:int, errors:string[]}
	 */
	public static function import_vehicles( string $csv_path ): array {
		$report     = self::new_report();
		$repository = new Vehicles_Repository();

		foreach ( self::read_csv( $csv_path ) as $line => $row ) {
			$id = self::require_uuid( $row['id'] ?? '', $line, $report, 'id' );
			if ( ! $id ) {
				continue;
			}
			if ( empty( $row['name'] ) || empty( $row['model'] ) || empty( $row['license_plate'] ) || empty( $row['fuel_type'] ) ) {
				self::error( $report, $line, __( 'name/model/license_plate/fuel_type requis', 'sierra-logistics' ) );
				continue;
			}

			$status = $row['status'] ?? 'disponible';
			if ( ! in_array( $status, Vehicles_Repository::STATUSES, true ) ) {
				self::error( $report, $line, sprintf( 'status inconnu (%s)', $status ) );
				continue;
			}

			$repository->import_upsert(
				array(
					'id'            => $id,
					'name'          => sanitize_text_field( $row['name'] ),
					'model'         => sanitize_text_field( $row['model'] ),
					'license_plate' => sanitize_text_field( $row['license_plate'] ),
					'fuel_type'     => sanitize_text_field( $row['fuel_type'] ),
					'status'        => $status,
					'contact_phone' => sanitize_text_field( $row['contact_phone'] ?? '' ),
					'created_at'    => self::to_utc_datetime( $row['created_at'] ?? '' ) ?? gmdate( 'Y-m-d H:i:s' ),
				)
			);
			++$report['imported'];
		}

		return $report;
	}

	/**
	 * Importe le CSV des devis. Chaque devis importé sans invoice_number
	 * existant s'en voit attribuer un (données historiques jamais numérotées
	 * jusqu'ici, voir migration/AUDIT.md §5).
	 *
	 * @param string $csv_path Chemin du fichier CSV.
	 *
	 * @return array{imported:int, skipped:int, errors:string[]}
	 */
	public static function import_quotes( string $csv_path ): array {
		$report     = self::new_report();
		$repository = new Quotes_Repository();

		foreach ( self::read_csv( $csv_path ) as $line => $row ) {
			$id = self::require_uuid( $row['id'] ?? '', $line, $report, 'id' );
			if ( ! $id ) {
				continue;
			}

			$statut = $row['statut'] ?? 'en_attente';
			if ( ! in_array( $statut, Quotes_Repository::STATUSES, true ) ) {
				self::error( $report, $line, sprintf( 'statut inconnu (%s)', $statut ) );
				continue;
			}

			$existing       = $repository->find( $id );
			$invoice_number = $existing['invoice_number'] ?? null;
			if ( ! $invoice_number ) {
				$invoice_number = Invoice_Numbering::next_proforma();
			}

			$repository->import_upsert(
				array(
					'id'                   => $id,
					'nom'                  => sanitize_text_field( $row['nom'] ?? '' ),
					'email'                => sanitize_email( $row['email'] ?? '' ),
					'telephone'            => sanitize_text_field( $row['telephone'] ?? '' ),
					'ville_depart'         => sanitize_text_field( $row['ville_depart'] ?? '' ),
					'ville_arrivee'        => sanitize_text_field( $row['ville_arrivee'] ?? '' ),
					'type_marchandise'     => sanitize_text_field( $row['type_marchandise'] ?? '' ),
					'poids'                => (float) ( $row['poids'] ?? 0 ),
					'type_vehicle'         => sanitize_text_field( $row['type_vehicle'] ?? '' ),
					'date_expedition'      => self::to_date( $row['date_expedition'] ?? '' ),
					'infos_additionnelles' => sanitize_textarea_field( $row['infos_additionnelles'] ?? '' ),
					'distance'             => (float) ( $row['distance'] ?? 0 ),
					'zone'                 => sanitize_text_field( $row['zone'] ?? '' ),
					'tarif_zone'           => (float) ( $row['tarif_zone'] ?? 0 ),
					'coefficient_camion'   => (float) ( $row['coefficient_camion'] ?? 1 ),
					'montant_transport'    => (float) ( $row['montant_transport'] ?? 0 ),
					'majoration'           => (float) ( $row['majoration'] ?? 0 ),
					'sous_total'           => (float) ( $row['sous_total'] ?? 0 ),
					'tva'                  => (float) ( $row['tva'] ?? 0 ),
					'total'                => (float) ( $row['total'] ?? 0 ),
					'statut'               => $statut,
					'invoice_number'       => $invoice_number,
					'created_at'           => self::to_utc_datetime( $row['created_at'] ?? '' ) ?? gmdate( 'Y-m-d H:i:s' ),
				)
			);
			++$report['imported'];
		}

		return $report;
	}

	/**
	 * Importe le CSV des commandes. Chaque commande importée sans
	 * invoice_number existant s'en voit attribuer un.
	 *
	 * @param string $csv_path Chemin du fichier CSV.
	 *
	 * @return array{imported:int, skipped:int, errors:string[]}
	 */
	public static function import_commandes( string $csv_path ): array {
		$report     = self::new_report();
		$repository = new Commandes_Repository();

		foreach ( self::read_csv( $csv_path ) as $line => $row ) {
			$id          = self::require_uuid( $row['id'] ?? '', $line, $report, 'id' );
			$proforma_id = self::require_uuid( $row['proforma_id'] ?? '', $line, $report, 'proforma_id' );
			$vehicle_id  = self::require_uuid( $row['vehicle_id'] ?? '', $line, $report, 'vehicle_id' );
			if ( ! $id || ! $proforma_id || ! $vehicle_id ) {
				continue;
			}
			if ( empty( $row['camion_immatriculation'] ) || empty( $row['chauffeur'] ) ) {
				self::error( $report, $line, __( 'camion_immatriculation/chauffeur requis', 'sierra-logistics' ) );
				continue;
			}

			$existing       = $repository->find_by_proforma( $proforma_id );
			$invoice_number = $existing['invoice_number'] ?? null;
			if ( ! $invoice_number ) {
				$invoice_number = Invoice_Numbering::next_definitive();
			}

			$repository->import_upsert(
				array(
					'id'                     => $id,
					'proforma_id'            => $proforma_id,
					'vehicle_id'             => $vehicle_id,
					'camion_immatriculation' => sanitize_text_field( $row['camion_immatriculation'] ),
					'chauffeur'              => sanitize_text_field( $row['chauffeur'] ),
					'telephone_chauffeur'    => sanitize_text_field( $row['telephone_chauffeur'] ?? '' ),
					'invoice_number'         => $invoice_number,
					'date_validation'        => self::to_utc_datetime( $row['date_validation'] ?? '' ) ?? gmdate( 'Y-m-d H:i:s' ),
				)
			);
			++$report['imported'];
		}

		return $report;
	}

	/**
	 * Importe le CSV des admins Supabase : crée un compte WordPress par ligne
	 * (rôle sierra_admin/sierra_agent), avec un lien de définition de mot de
	 * passe envoyé par e-mail (les mots de passe Supabase ne sont pas
	 * récupérables). Idempotent via la usermeta META_LEGACY_ADMIN_ID.
	 *
	 * @param string $csv_path        Chemin du CSV admins (id,name,role,created_at).
	 * @param string $emails_csv_path Chemin optionnel d'un CSV complémentaire (id,email).
	 *
	 * @return array{imported:int, skipped:int, errors:string[]}
	 */
	public static function import_admins( string $csv_path, string $emails_csv_path = '' ): array {
		$report = self::new_report();
		$emails = array();

		if ( $emails_csv_path ) {
			foreach ( self::read_csv( $emails_csv_path ) as $row ) {
				if ( ! empty( $row['id'] ) && ! empty( $row['email'] ) ) {
					$emails[ $row['id'] ] = $row['email'];
				}
			}
		}

		foreach ( self::read_csv( $csv_path ) as $line => $row ) {
			$legacy_id = self::require_uuid( $row['id'] ?? '', $line, $report, 'id' );
			if ( ! $legacy_id ) {
				continue;
			}

			$role  = 'admin' === ( $row['role'] ?? 'agent' ) ? Capabilities::ROLE_ADMIN : Capabilities::ROLE_AGENT;
			$name  = sanitize_text_field( $row['name'] ?? '' );
			$email = sanitize_email( $row['email'] ?? ( $emails[ $legacy_id ] ?? '' ) );

			if ( ! $email || ! is_email( $email ) ) {
				self::error(
					$report,
					$line,
					sprintf(
						/* translators: %s: identifiant Supabase de l'admin */
						__( 'email manquant pour %s : fournissez le CSV complémentaire --emails ou éditez le compte après import', 'sierra-logistics' ),
						$legacy_id
					)
				);
				continue;
			}

			$existing_user_id = self::find_user_by_legacy_id( $legacy_id );
			if ( $existing_user_id ) {
				wp_update_user(
					array(
						'ID'   => $existing_user_id,
						'role' => $role,
					)
				);
				++$report['imported'];
				continue;
			}

			$user_by_email = get_user_by( 'email', $email );
			if ( $user_by_email ) {
				update_user_meta( $user_by_email->ID, self::META_LEGACY_ADMIN_ID, $legacy_id );
				wp_update_user(
					array(
						'ID'   => $user_by_email->ID,
						'role' => $role,
					)
				);
				++$report['imported'];
				continue;
			}

			$user_id = wp_insert_user(
				array(
					'user_login'   => self::unique_login( $email ),
					'user_email'   => $email,
					'display_name' => $name ? $name : $email,
					'user_pass'    => wp_generate_password( 24 ),
					'role'         => $role,
				)
			);

			if ( is_wp_error( $user_id ) ) {
				self::error( $report, $line, $user_id->get_error_message() );
				continue;
			}

			update_user_meta( $user_id, self::META_LEGACY_ADMIN_ID, $legacy_id );
			wp_new_user_notification( $user_id, null, 'user' );
			++$report['imported'];
		}

		return $report;
	}

	/**
	 * Retrouve un utilisateur déjà importé par son id Supabase d'origine.
	 *
	 * @param string $legacy_id UUID Supabase de l'admin.
	 */
	private static function find_user_by_legacy_id( string $legacy_id ): ?int {
		$users = get_users(
			array(
				'meta_key'   => self::META_LEGACY_ADMIN_ID, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- table des utilisateurs, volumétrie faible (comptes internes), pas de pagination nécessaire.
				'meta_value' => $legacy_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'number'     => 1,
				'fields'     => 'ID',
			)
		);
		return ! empty( $users ) ? (int) $users[0] : null;
	}

	/**
	 * Construit un identifiant de connexion unique à partir d'un e-mail.
	 *
	 * @param string $email Adresse e-mail du compte.
	 */
	private static function unique_login( string $email ): string {
		$base  = sanitize_user( current( explode( '@', $email ) ), true );
		$login = $base;
		$i     = 1;
		while ( username_exists( $login ) ) {
			++$i;
			$login = $base . $i;
		}
		return $login;
	}

	/**
	 * Lit un CSV et retourne chaque ligne comme tableau associatif
	 * (colonnes = première ligne), avec le numéro de ligne d'origine.
	 *
	 * @param string $path Chemin du fichier CSV.
	 *
	 * @return \Generator<int, array<string, string>>
	 */
	private static function read_csv( string $path ): \Generator {
		$handle = fopen( $path, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen -- lecture d'un fichier CSV temporaire uploadé, hors du système de fichiers WordPress.
		if ( ! $handle ) {
			return;
		}

		$headers = fgetcsv( $handle );
		$line    = 1;

		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			++$line;
			if ( count( $headers ) !== count( $row ) ) {
				continue;
			}
			yield $line => array_combine( $headers, $row );
		}

		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
	}

	/**
	 * Convertit un timestamptz Supabase (ISO 8601) en 'Y-m-d H:i:s' UTC.
	 *
	 * @param string $value Valeur brute du CSV.
	 */
	private static function to_utc_datetime( string $value ): ?string {
		if ( '' === trim( $value ) ) {
			return null;
		}
		try {
			$date = new \DateTime( $value );
			$date->setTimezone( new \DateTimeZone( 'UTC' ) );
			return $date->format( 'Y-m-d H:i:s' );
		} catch ( \Exception $e ) {
			return null;
		}
	}

	/**
	 * Convertit une date Supabase en 'Y-m-d'.
	 *
	 * @param string $value Valeur brute du CSV.
	 */
	private static function to_date( string $value ): ?string {
		if ( '' === trim( $value ) ) {
			return null;
		}
		try {
			return ( new \DateTime( $value ) )->format( 'Y-m-d' );
		} catch ( \Exception $e ) {
			return null;
		}
	}

	/**
	 * Valide un UUID, ou journalise une erreur dans le rapport.
	 *
	 * @param string $value  Valeur à valider.
	 * @param int    $line   Numéro de ligne (pour le rapport).
	 * @param array  $report Rapport en cours, modifié par référence.
	 * @param string $field  Nom du champ (pour le message d'erreur).
	 */
	private static function require_uuid( string $value, int $line, array &$report, string $field ): ?string {
		if ( ! preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', trim( $value ) ) ) {
			self::error( $report, $line, sprintf( '%s invalide ou manquant (%s)', $field, $value ) );
			return null;
		}
		return trim( $value );
	}

	/**
	 * Ajoute une erreur au rapport.
	 *
	 * @param array  $report  Rapport en cours, modifié par référence.
	 * @param int    $line    Numéro de ligne.
	 * @param string $message Message d'erreur.
	 */
	private static function error( array &$report, int $line, string $message ): void {
		++$report['skipped'];
		$report['errors'][] = sprintf( 'ligne %d : %s', $line, $message );
	}

	/**
	 * Rapport vide.
	 *
	 * @return array{imported:int, skipped:int, errors:string[]}
	 */
	private static function new_report(): array {
		return array(
			'imported' => 0,
			'skipped'  => 0,
			'errors'   => array(),
		);
	}
}
