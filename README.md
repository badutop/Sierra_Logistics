# Sierra Logistics

Site de Sierra Logistics (transport de marchandises, Sénégal / CEDEAO) :
front Next.js exporté en statique, couplé à un back-office WordPress/MySQL
(plugin `wordpress/wp-content/plugins/sierra-logistics-core` et thème
`wordpress/wp-content/themes/sierra-gestion`).

Migré depuis une version précédente sur Supabase. Contexte complet de la
migration : `migration/AUDIT.md`.

## Structure du dépôt

```
src/                  Front Next.js (site public + /api, client REST)
public/               Images et assets statiques du front
wordpress/
  wp-config.php                   Config WordPress commune (committée)
  wp-config-local.php.example     Modèle pour la production (jamais commité une fois copié)
  wp-content/plugins/sierra-logistics-core/   Plugin : devis, commandes, véhicules, facturation
  wp-content/themes/sierra-gestion/           Thème minimal (aucun rendu public)
docker/                Environnement de développement local (voir plus bas)
migration/             Audit, scripts et procédure de migration/déploiement
```

## Installation

Prérequis : Node.js ≥ 20.9, Docker (pour l'environnement local complet),
Composer (uniquement nécessaire pour vendoriser Dompdf, voir Déploiement).

```bash
npm install
```

## Développement local

### Environnement complet (front + WordPress + MySQL)

Démarre en une seule commande tout ce dont vous avez besoin : WordPress,
MariaDB, phpMyAdmin, et le serveur de dev du front, derrière un reverse
proxy nginx qui reproduit la topologie de production (`/` → front,
`/gestion/` → WordPress) sur un seul port.

```bash
cp docker/.env.example docker/.env
docker compose up -d
```

- Site complet (front + `/gestion/`) : http://localhost:8080
- Installation WordPress : http://localhost:8080/gestion/wp-admin/install.php
- phpMyAdmin : http://localhost:8081
- Front seul (sans passer par le proxy) : http://localhost:3000

**Première installation** :

1. Terminez l'installation WordPress à l'adresse ci-dessus.
2. Activez le plugin **Sierra Logistics Core** et le thème **Sierra
   Gestion** (Extensions / Apparence).
3. **Important** : réglez les permaliens sur un format autre que "Simple"
   (Réglages > Permaliens, ex. "Titre de la publication") et enregistrez.
   Sans ça, l'API REST (`/gestion/wp-json/sierra/v1/...`) répond par une
   redirection au lieu du JSON attendu - une installation fraîche n'a pas
   encore de règles de réécriture.
4. Le compte administrateur créé à l'installation a automatiquement accès
   au back-office métier. Pour tester la distinction de droits, créez aussi
   un compte avec le rôle **Agent Sierra Logistics**.

```bash
docker compose down        # arrête les conteneurs, conserve les données
docker compose down -v     # arrête et supprime aussi la base et les fichiers WordPress
```

### Front seul

Si vous n'avez pas besoin de WordPress (travail purement visuel sur le site
public) :

```bash
npm run dev
```

Les appels à l'API (`src/api/client.js`) utilisent un chemin relatif
(`/gestion/wp-json/sierra/v1`) : sans le proxy Docker, ils échoueront en
local, sauf si `NEXT_PUBLIC_API_URL` est défini vers une instance WordPress
déjà en ligne.

### Qualité du plugin WordPress

```bash
cd wordpress/wp-content/plugins/sierra-logistics-core
composer install
composer exec phpunit    # tests unitaires (calcul des devis, etc.)
composer exec phpcs      # WordPress Coding Standards
```

## Build de production

```bash
npm run build
```

Produit un export statique dans `out/` (voir `next.config.mjs`,
`output: "export"`), déployé tel quel sur l'hébergement mutualisé OVH.

## Déploiement

Procédure complète (première installation sur OVH, puis mises à jour) :
`migration/DEPLOIEMENT.md`. Résumé :

```bash
cp migration/deploy.env.example migration/deploy.env   # une fois, à compléter
./migration/deploy.sh front        # build + déploie le front vers www/
./migration/deploy.sh wordpress    # vendorise Dompdf + déploie plugin/thème vers www/gestion/wp-content/
./migration/deploy.sh all          # les deux
```

Le script ne touche jamais `www/gestion/` depuis le déploiement du front, ni
le reste de `www/gestion/` (wp-config-local.php, uploads...) depuis le
déploiement du plugin/thème.

La bascule du sous-domaine temporaire vers le domaine définitif est détaillée
dans la même procédure (`migration/DEPLOIEMENT.md`).

## Recette

Une fois le site installé : `migration/CHECKLIST-RECETTE.md`, à suivre pas à
pas avant de considérer la migration terminée.

## Migration des données

Export Supabase → MySQL : `migration/sierra_schema_mysql.sql` (schéma de
référence) et `migration/convert_csv_to_mysql.py` (conversion CSV → SQL,
utilitaire autonome). En production, l'import se fait plutôt depuis
wp-admin (Sierra Logistics > Import) ou en ligne de commande (`wp sierra
import ...`), voir `migration/DEPLOIEMENT.md`.
