#!/usr/bin/env python3
"""Convertit les CSV exportés depuis Supabase (Table Editor > Export CSV) en
instructions SQL MySQL prêtes à importer dans le schéma de
sierra_schema_mysql.sql.

Usage :
    python3 convert_csv_to_mysql.py --input-dir ./export --output-dir ./sql

Fichiers attendus dans --input-dir (noms exacts, un par table Supabase) :
    vehicles.csv, quotes.csv, commandes.csv, admins.csv

admins.csv ne produit PAS de SQL (il n'y a pas de table wp_sierra_admins,
voir le commentaire en tête de sierra_schema_mysql.sql : les comptes
deviennent des wp_users natifs). Ce script normalise seulement les lignes
dans admins_normalized.csv, destiné à la commande WP-CLI d'import du plugin
(wp sierra import admins), qui crée les comptes WordPress. Les mots de passe
Supabase ne sont pas récupérables : fournissez un CSV complémentaire
--admin-emails (colonnes : id,email) si les emails ne sont pas dans
admins.csv, sinon la colonne email sera vide dans la sortie et devra être
complétée à la main avant l'import WP-CLI.

Ordre d'exécution : vehicles -> quotes -> commandes -> admins (respecté
automatiquement par ce script, qui ignore l'ordre des arguments).

Idempotent : chaque INSERT est un `INSERT ... ON DUPLICATE KEY UPDATE`
indexé sur l'id (UUID conservé depuis Supabase) : relancer le script sur le
même export ne crée jamais de doublon, il met juste à jour les mêmes lignes.
"""

import argparse
import csv
import re
import sys
from datetime import datetime, timezone
from pathlib import Path

UUID_RE = re.compile(
    r"^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$", re.IGNORECASE
)


class ImportReport:
    def __init__(self, table):
        self.table = table
        self.imported = 0
        self.skipped = 0
        self.errors = []

    def error(self, row_num, message):
        self.skipped += 1
        self.errors.append(f"  ligne {row_num}: {message}")

    def ok(self):
        self.imported += 1

    def summary(self):
        lines = [
            f"{self.table}: {self.imported} importées, {self.skipped} ignorées/erreurs"
        ]
        lines.extend(self.errors)
        return "\n".join(lines)


def sql_escape(value):
    """Échappe une valeur pour une instruction SQL littérale. Pour un usage
    ponctuel de migration uniquement - jamais pour construire des requêtes à
    partir d'une entrée utilisateur en production (le plugin PHP utilise
    $wpdb->prepare() partout, voir AGENTS du dépôt)."""
    if value is None or value == "":
        return "NULL"
    text = str(value).replace("\\", "\\\\").replace("'", "''")
    return f"'{text}'"


def sql_number(value):
    if value is None or str(value).strip() == "":
        return "NULL"
    try:
        float(value)
    except ValueError:
        return "NULL"
    return str(value)


def to_utc_datetime(value):
    """Convertit un timestamptz Supabase (ISO 8601, souvent suffixé Z ou
    +00:00) en 'YYYY-MM-DD HH:MM:SS' UTC pour une colonne DATETIME MySQL."""
    if not value:
        return None
    text = value.strip().replace("Z", "+00:00")
    try:
        dt = datetime.fromisoformat(text)
    except ValueError:
        return None
    if dt.tzinfo is not None:
        dt = dt.astimezone(timezone.utc).replace(tzinfo=None)
    return dt.strftime("%Y-%m-%d %H:%M:%S")


def to_date(value):
    if not value:
        return None
    text = value.strip()
    for fmt in ("%Y-%m-%d", "%Y-%m-%dT%H:%M:%S"):
        try:
            return datetime.strptime(text[: len(fmt) + 2], fmt).strftime("%Y-%m-%d")
        except ValueError:
            continue
    return None


def require_uuid(value, row_num, report, field):
    if not value or not UUID_RE.match(value.strip()):
        report.error(row_num, f"{field} invalide ou manquant ({value!r})")
        return None
    return value.strip()


def convert_vehicles(input_dir, out):
    path = input_dir / "vehicles.csv"
    report = ImportReport("vehicles")
    if not path.exists():
        print(f"(ignoré) {path} absent")
        return report

    statements = []
    with path.open(newline="", encoding="utf-8") as f:
        for i, row in enumerate(csv.DictReader(f), start=2):
            vid = require_uuid(row.get("id"), i, report, "id")
            if not vid:
                continue
            if not row.get("name") or not row.get("model") or not row.get("license_plate") or not row.get("fuel_type"):
                report.error(i, "name/model/license_plate/fuel_type requis")
                continue
            status = (row.get("status") or "disponible").strip()
            if status not in ("disponible", "en_course", "maintenance"):
                report.error(i, f"status inconnu ({status!r}), ignorée")
                continue
            created_at = to_utc_datetime(row.get("created_at")) or "UTC_TIMESTAMP()"
            created_sql = created_at if created_at == "UTC_TIMESTAMP()" else sql_escape(created_at)

            statements.append(
                "INSERT INTO `wp_sierra_vehicles` "
                "(id, name, model, license_plate, fuel_type, status, contact_phone, created_at) VALUES "
                f"({sql_escape(vid)}, {sql_escape(row['name'])}, {sql_escape(row['model'])}, "
                f"{sql_escape(row['license_plate'])}, {sql_escape(row['fuel_type'])}, "
                f"{sql_escape(status)}, {sql_escape(row.get('contact_phone'))}, {created_sql}) "
                "ON DUPLICATE KEY UPDATE name=VALUES(name), model=VALUES(model), "
                "license_plate=VALUES(license_plate), fuel_type=VALUES(fuel_type), "
                "status=VALUES(status), contact_phone=VALUES(contact_phone);"
            )
            report.ok()

    (out / "01_vehicles.sql").write_text("\n".join(statements) + "\n", encoding="utf-8")
    return report


def convert_quotes(input_dir, out):
    path = input_dir / "quotes.csv"
    report = ImportReport("quotes")
    if not path.exists():
        print(f"(ignoré) {path} absent")
        return report

    statements = []
    with path.open(newline="", encoding="utf-8") as f:
        for i, row in enumerate(csv.DictReader(f), start=2):
            qid = require_uuid(row.get("id"), i, report, "id")
            if not qid:
                continue
            statut = (row.get("statut") or "en_attente").strip()
            if statut not in ("en_attente", "commandé"):
                report.error(i, f"statut inconnu ({statut!r}), ignorée")
                continue
            created_at = to_utc_datetime(row.get("created_at")) or "UTC_TIMESTAMP()"
            created_sql = created_at if created_at == "UTC_TIMESTAMP()" else sql_escape(created_at)
            date_expedition = to_date(row.get("date_expedition"))

            columns = [
                "id", "nom", "email", "telephone", "ville_depart", "ville_arrivee",
                "type_marchandise", "poids", "type_vehicle", "date_expedition",
                "infos_additionnelles", "distance", "zone", "tarif_zone",
                "coefficient_camion", "montant_transport", "majoration",
                "sous_total", "tva", "total", "statut", "created_at",
            ]
            values = [
                sql_escape(qid), sql_escape(row.get("nom")), sql_escape(row.get("email")),
                sql_escape(row.get("telephone")), sql_escape(row.get("ville_depart")),
                sql_escape(row.get("ville_arrivee")), sql_escape(row.get("type_marchandise")),
                sql_number(row.get("poids")), sql_escape(row.get("type_vehicle")),
                sql_escape(date_expedition), sql_escape(row.get("infos_additionnelles")),
                sql_number(row.get("distance")), sql_escape(row.get("zone")),
                sql_number(row.get("tarif_zone")), sql_number(row.get("coefficient_camion")),
                sql_number(row.get("montant_transport")), sql_number(row.get("majoration")),
                sql_number(row.get("sous_total")), sql_number(row.get("tva")),
                sql_number(row.get("total")), sql_escape(statut), created_sql,
            ]
            update_cols = [c for c in columns if c not in ("id", "created_at")]
            statements.append(
                f"INSERT INTO `wp_sierra_quotes` ({', '.join(columns)}) VALUES "
                f"({', '.join(values)}) ON DUPLICATE KEY UPDATE "
                + ", ".join(f"{c}=VALUES({c})" for c in update_cols)
                + ";"
            )
            report.ok()

    (out / "02_quotes.sql").write_text("\n".join(statements) + "\n", encoding="utf-8")
    return report


def convert_commandes(input_dir, out):
    path = input_dir / "commandes.csv"
    report = ImportReport("commandes")
    if not path.exists():
        print(f"(ignoré) {path} absent")
        return report

    statements = []
    with path.open(newline="", encoding="utf-8") as f:
        for i, row in enumerate(csv.DictReader(f), start=2):
            cid = require_uuid(row.get("id"), i, report, "id")
            proforma_id = require_uuid(row.get("proforma_id"), i, report, "proforma_id")
            vehicle_id = require_uuid(row.get("vehicle_id"), i, report, "vehicle_id")
            if not (cid and proforma_id and vehicle_id):
                continue
            if not row.get("camion_immatriculation") or not row.get("chauffeur"):
                report.error(i, "camion_immatriculation/chauffeur requis")
                continue
            date_validation = to_utc_datetime(row.get("date_validation")) or "UTC_TIMESTAMP()"
            date_sql = date_validation if date_validation == "UTC_TIMESTAMP()" else sql_escape(date_validation)

            statements.append(
                "INSERT INTO `wp_sierra_commandes` "
                "(id, proforma_id, vehicle_id, camion_immatriculation, chauffeur, telephone_chauffeur, date_validation) VALUES "
                f"({sql_escape(cid)}, {sql_escape(proforma_id)}, {sql_escape(vehicle_id)}, "
                f"{sql_escape(row['camion_immatriculation'])}, {sql_escape(row['chauffeur'])}, "
                f"{sql_escape(row.get('telephone_chauffeur'))}, {date_sql}) "
                "ON DUPLICATE KEY UPDATE camion_immatriculation=VALUES(camion_immatriculation), "
                "chauffeur=VALUES(chauffeur), telephone_chauffeur=VALUES(telephone_chauffeur);"
            )
            report.ok()

    (out / "03_commandes.sql").write_text("\n".join(statements) + "\n", encoding="utf-8")
    return report


def normalize_admins(input_dir, out, admin_emails_path):
    path = input_dir / "admins.csv"
    report = ImportReport("admins (normalisation, pas de SQL)")
    if not path.exists():
        print(f"(ignoré) {path} absent")
        return report

    emails_by_id = {}
    if admin_emails_path and admin_emails_path.exists():
        with admin_emails_path.open(newline="", encoding="utf-8") as f:
            for row in csv.DictReader(f):
                if row.get("id"):
                    emails_by_id[row["id"].strip()] = (row.get("email") or "").strip()

    rows_out = []
    with path.open(newline="", encoding="utf-8") as f:
        for i, row in enumerate(csv.DictReader(f), start=2):
            legacy_id = require_uuid(row.get("id"), i, report, "id")
            if not legacy_id:
                continue
            role = (row.get("role") or "agent").strip()
            if role not in ("admin", "agent"):
                report.error(i, f"role inconnu ({role!r}), ignorée")
                continue
            email = emails_by_id.get(legacy_id, "")
            rows_out.append(
                {
                    "legacy_id": legacy_id,
                    "name": row.get("name") or "",
                    "email": email,
                    "role": "sierra_admin" if role == "admin" else "sierra_agent",
                }
            )
            report.ok()

    out_path = out / "admins_normalized.csv"
    with out_path.open("w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=["legacy_id", "name", "email", "role"])
        writer.writeheader()
        writer.writerows(rows_out)

    missing_emails = [r["legacy_id"] for r in rows_out if not r["email"]]
    if missing_emails:
        print(
            f"ATTENTION: {len(missing_emails)} admin(s) sans email dans admins_normalized.csv "
            "- complétez la colonne 'email' à la main avant 'wp sierra import admins'."
        )
    return report


def main():
    parser = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    parser.add_argument("--input-dir", required=True, type=Path, help="Dossier contenant les CSV exportés de Supabase")
    parser.add_argument("--output-dir", required=True, type=Path, help="Dossier de sortie pour les .sql et le rapport")
    parser.add_argument("--admin-emails", type=Path, default=None, help="CSV optionnel (colonnes: id,email) pour compléter admins.csv")
    args = parser.parse_args()

    if not args.input_dir.is_dir():
        sys.exit(f"Dossier introuvable : {args.input_dir}")
    args.output_dir.mkdir(parents=True, exist_ok=True)

    reports = [
        convert_vehicles(args.input_dir, args.output_dir),
        convert_quotes(args.input_dir, args.output_dir),
        convert_commandes(args.input_dir, args.output_dir),
        normalize_admins(args.input_dir, args.output_dir, args.admin_emails),
    ]

    report_text = "\n\n".join(r.summary() for r in reports)
    (args.output_dir / "import_report.txt").write_text(report_text + "\n", encoding="utf-8")
    print("\n" + report_text)
    print(f"\nSQL et rapport écrits dans {args.output_dir}")
    print(
        "Import MySQL : mysql -u <user> -p <base> < "
        f"{args.output_dir}/01_vehicles.sql (puis 02_quotes.sql, 03_commandes.sql dans cet ordre)."
    )


if __name__ == "__main__":
    main()
