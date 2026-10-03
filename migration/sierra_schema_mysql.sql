-- Sierra Logistics - schéma MySQL cible (remplace le projet Supabase/Postgres audité).
--
-- Portée : uniquement les données métier (véhicules, devis, commandes).
-- Les comptes "admins" du schéma Supabase ne sont PAS repris comme table à part :
-- WordPress a déjà un système de comptes/rôles natif (wp_users, wp_usermeta,
-- wp_user_roles). L'équivalent du rôle Supabase `admins.role` devient les rôles
-- WordPress personnalisés `sierra_admin` / `sierra_agent` (voir le plugin
-- sierra-logistics-core, classe Capabilities). Le lien avec l'ancien id Supabase
-- est conservé en usermeta (`sierra_legacy_admin_id`), pas en table dédiée.
--
-- Préfixe : ce fichier utilise le préfixe par défaut `wp_`. En production le
-- plugin crée ces tables via dbDelta() avec `$wpdb->prefix` (voir
-- includes/class-schema.php) : si votre préfixe WordPress n'est pas `wp_`,
-- adaptez ce fichier avant tout import manuel hors du plugin (phpMyAdmin,
-- ligne de commande). dbDelta() ne sait pas créer de contraintes FOREIGN KEY
-- (elles sont silencieusement ignorées lors des mises à jour de schéma) :
-- l'intégrité référentielle entre quotes/commandes/vehicles est donc imposée
-- par les classes repository du plugin, pas par la base. Les FOREIGN KEY
-- ci-dessous sont présentes uniquement pour un import manuel direct en MySQL
-- (hors WordPress), où elles sont utiles et sans danger.

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------------
-- wp_sierra_vehicles : camions inscrits par les transporteurs partenaires
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wp_sierra_vehicles` (
  `id` CHAR(36) NOT NULL COMMENT 'UUID, conservé tel quel depuis Supabase',
  `name` VARCHAR(255) NOT NULL COMMENT 'Nom du propriétaire/transporteur',
  `model` VARCHAR(100) NOT NULL COMMENT 'Type de camion, voir TRUCK_TYPES côté front',
  `license_plate` VARCHAR(50) NOT NULL,
  `fuel_type` VARCHAR(50) NOT NULL,
  `status` ENUM('disponible', 'en_course', 'maintenance') NOT NULL DEFAULT 'disponible',
  `contact_phone` VARCHAR(50) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'UTC',
  PRIMARY KEY (`id`),
  KEY `vehicles_status_idx` (`status`),
  KEY `vehicles_model_idx` (`model`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ---------------------------------------------------------------------------
-- wp_sierra_quotes : devis générés depuis le formulaire public /devis
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wp_sierra_quotes` (
  `id` CHAR(36) NOT NULL COMMENT 'UUID, conservé tel quel depuis Supabase',
  `nom` VARCHAR(255) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `telephone` VARCHAR(50) DEFAULT NULL,
  `ville_depart` VARCHAR(100) DEFAULT NULL,
  `ville_arrivee` VARCHAR(100) DEFAULT NULL,
  `type_marchandise` VARCHAR(100) DEFAULT NULL,
  `poids` DECIMAL(12,2) DEFAULT NULL COMMENT 'kg',
  `type_vehicle` VARCHAR(100) DEFAULT NULL,
  `date_expedition` DATE DEFAULT NULL,
  `infos_additionnelles` TEXT,
  `distance` DECIMAL(10,2) DEFAULT NULL COMMENT 'km, approximation par différence de distance à Dakar',
  `zone` VARCHAR(100) DEFAULT NULL COMMENT 'libellé de la zone tarifaire retenue',
  `tarif_zone` DECIMAL(12,2) DEFAULT NULL COMMENT 'FCFA/km',
  `coefficient_camion` DECIMAL(5,2) DEFAULT NULL,
  `montant_transport` DECIMAL(14,2) DEFAULT NULL COMMENT 'FCFA',
  `majoration` DECIMAL(14,2) DEFAULT NULL COMMENT 'FCFA',
  `sous_total` DECIMAL(14,2) DEFAULT NULL COMMENT 'FCFA',
  `tva` DECIMAL(14,2) DEFAULT NULL COMMENT 'FCFA, 18% du sous-total',
  `total` DECIMAL(14,2) DEFAULT NULL COMMENT 'FCFA',
  `statut` ENUM('en_attente', 'commandé') NOT NULL DEFAULT 'en_attente',
  `invoice_number` VARCHAR(30) DEFAULT NULL COMMENT 'Nouveau : numérotation séquentielle sans trou (absente du schéma Supabase)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'UTC',
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotes_invoice_number_uq` (`invoice_number`),
  KEY `quotes_telephone_idx` (`telephone`),
  KEY `quotes_statut_idx` (`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ---------------------------------------------------------------------------
-- wp_sierra_commandes : affectation camion/chauffeur à un devis validé
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wp_sierra_commandes` (
  `id` CHAR(36) NOT NULL COMMENT 'UUID, conservé tel quel depuis Supabase',
  `proforma_id` CHAR(36) NOT NULL COMMENT 'wp_sierra_quotes.id, unique : un seul commande par devis',
  `vehicle_id` CHAR(36) NOT NULL COMMENT 'wp_sierra_vehicles.id',
  `camion_immatriculation` VARCHAR(50) NOT NULL,
  `chauffeur` VARCHAR(255) NOT NULL,
  `telephone_chauffeur` VARCHAR(50) DEFAULT NULL,
  `invoice_number` VARCHAR(30) DEFAULT NULL COMMENT 'Nouveau : numéro de facture définitive, séquentiel sans trou',
  `date_validation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'UTC',
  PRIMARY KEY (`id`),
  UNIQUE KEY `commandes_proforma_id_uq` (`proforma_id`),
  UNIQUE KEY `commandes_invoice_number_uq` (`invoice_number`),
  KEY `commandes_vehicle_id_idx` (`vehicle_id`),
  CONSTRAINT `fk_commandes_proforma` FOREIGN KEY (`proforma_id`)
    REFERENCES `wp_sierra_quotes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_commandes_vehicle` FOREIGN KEY (`vehicle_id`)
    REFERENCES `wp_sierra_vehicles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
