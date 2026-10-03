# Déploiement Sierra Logistics

Le site est d'abord mis en ligne sur un **sous-domaine temporaire** (ex.
`sierra-logistics-temp.exemple.sn`), le temps de vérifier la bascule, avant
son **domaine définitif**. Cette procédure couvre l'installation initiale ;
la section "Bascule vers le domaine définitif" plus bas couvre le
changement d'adresse, qui doit rester une opération de configuration,
jamais de code.

## Installation initiale sur OVH

### 1. Créer la base MySQL

Espace client OVH > Hébergements > votre hébergement > Bases de données >
Créer une base. Notez le nom de la base, l'utilisateur, le mot de passe et
le serveur fourni (du type `xxxxx.mysql.db`) : ce sont les quatre valeurs à
mettre dans `wp-config-local.php` à l'étape 3.

### 2. Préparer l'arborescence sur le serveur

Par (S)FTP, sous la racine de l'hébergement (`www/`) :

```
www/
  gestion/        <- WordPress (core + wp-content/plugins/themes du plugin)
  .htaccess       <- copie de migration/www.htaccess
  (le reste de www/ sera rempli par le build du front, étape 10)
```

Uploadez `migration/www.htaccess` en tant que `www/.htaccess`. Il contient
déjà le blocage d'indexation du sous-domaine temporaire (voir plus bas) et
la redirection `/expedier -> /devis`.

### 3. Installer WordPress dans /gestion/

Téléchargez la dernière version de WordPress (wordpress.org/download),
uploadez son contenu dans `www/gestion/`, puis :

1. Copiez `wordpress/wp-config.php` (ce dépôt) vers `www/gestion/wp-config.php`.
2. Copiez `wordpress/wp-config-local.php.example` vers `www/gestion/wp-config-local.php`
   et complétez-le avec les identifiants de la base (étape 1), `WP_HOME`
   (`https://sous-domaine-temporaire.exemple.sn`), `WP_SITEURL`
   (`https://sous-domaine-temporaire.exemple.sn/gestion`), `WP_ENVIRONMENT_TYPE=production`,
   et des clés de sécurité générées sur
   https://api.wordpress.org/secret-key/1.1/salt/. Ce fichier ne doit jamais
   être commité (déjà dans `.gitignore`).
3. Visitez `https://sous-domaine-temporaire.exemple.sn/gestion/wp-admin/install.php`
   et terminez l'installation (titre du site, compte administrateur).
4. **Réglages > Lecture** : cochez *"Demander aux moteurs de recherche de ne
   pas indexer ce site"* (sous-domaine temporaire, voir plus bas).

### 4. Déployer le plugin et le thème

Première fois : voir "Déployer le plugin et le thème" ci-dessous (ou
manuellement par SFTP, dans l'ordre : `composer install --no-dev` dans
`wordpress/wp-content/plugins/sierra-logistics-core`, puis copie de ce
dossier et de `wordpress/wp-content/themes/sierra-gestion` vers
`www/gestion/wp-content/plugins/` et `.../themes/`).

### 5. Activer le plugin, le thème, et régler les permaliens

Dans wp-admin : Extensions > activer **Sierra Logistics Core**. Apparence >
activer **Sierra Gestion**.

**Étape obligatoire** : Réglages > Permaliens > choisir un format autre que
"Simple" (ex. "Titre de la publication") > Enregistrer les modifications.
Une installation fraîche n'a aucune règle de réécriture tant que cette page
n'a pas été enregistrée une fois : sans ça, l'API REST publique
(`/gestion/wp-json/sierra/v1/...`) répond par une redirection cassée au lieu
du JSON attendu (vérifié en environnement Docker local, voir
`migration/AUDIT.md`).

### 6. Réglages Sierra Logistics

Menu **Sierra Logistics > Réglages** : coordonnées de l'entreprise (en-tête
des PDF), origine CORS autorisée (`https://sous-domaine-temporaire.exemple.sn`),
expéditeur et destinataire des e-mails, zones tarifaires/coefficients/TVA si
différents des valeurs par défaut (identiques à l'ancien site).

### 7. Importer les données Supabase

1. Exportez chaque table depuis Supabase (Table Editor > Export CSV) :
   `vehicles`, `quotes`, `commandes`, `admins`. Pour `admins`, préparez aussi
   un CSV `id,email` si les e-mails ne sont pas déjà dans l'export (les mots
   de passe Supabase ne sont de toute façon pas récupérables).
2. Menu **Sierra Logistics > Import** dans wp-admin : chargez les CSV
   (l'ordre vehicles → quotes → commandes → admins est géré automatiquement).
   Alternative en ligne de commande (WP-CLI, si disponible sur
   l'hébergement) :
   ```
   wp sierra import vehicles vehicles.csv
   wp sierra import quotes quotes.csv
   wp sierra import commandes commandes.csv
   wp sierra import admins admins.csv --emails=admin-emails.csv
   ```
3. Chaque nouvel admin reçoit un e-mail avec un lien de définition de mot de
   passe (WordPress natif, `wp_new_user_notification()`).

### 8. Vérifier l'import

- Dans **Sierra Logistics > Devis** et **> Commandes** : le nombre de lignes
  correspond au nombre de lignes des CSV exportés (hors éventuelles erreurs
  listées par le rapport d'import, à corriger et réimporter si besoin -
  l'import est idempotent, le relancer ne crée pas de doublon).
- **Sierra Logistics > Tableau de bord** : le "Chiffre d'affaires (validé)"
  affiché correspond à la somme des `total` des devis au statut `commandé`
  dans l'export Supabase (vérifiable avec une requête SQL simple sur le CSV,
  ou dans le dashboard Supabase avant la mise en pause).

### 9. Test de bout en bout

Sur le sous-domaine temporaire, **avant de déployer le nouveau front** (donc
en pointant temporairement un front de test, ou juste en testant l'API
directement) :
- `POST /gestion/wp-json/sierra/v1/quotes` avec des données de test : la
  réponse contient un `invoice_number` et des montants cohérents.
- Téléchargement du PDF depuis la fiche du devis créé.
- Validation de ce devis en commande (un véhicule disponible doit exister,
  via Véhicules ou l'import).

### 10. Déployer le front

```bash
cp migration/deploy.env.example migration/deploy.env   # une fois, à compléter
NEXT_PUBLIC_SITE_URL=https://sous-domaine-temporaire.exemple.sn \
NEXT_PUBLIC_ALLOW_INDEXING=false \
./migration/deploy.sh front
```

(Les deux variables `NEXT_PUBLIC_*` sont lues au build par `next build`,
voir "Principe directeur : zéro URL en dur" ci-dessous - les passer en
variables d'environnement au moment d'appeler le script, ou les définir
dans `.env.local` avant de lancer le déploiement.)

### 11. Geler puis mettre en pause Supabase

Une fois les étapes 8 et 9 validées : repassez le projet Supabase en lecture
seule (Database > Roles, ou simplement ne plus écrire dans l'ancien front
puisqu'il n'est plus déployé) pendant une période de vérification croisée
(quelques jours), puis mettez le projet en pause depuis le dashboard
Supabase. Ne rien supprimer côté Supabase (règle de travail : lecture et
export seulement).

## Déployer le plugin et le thème (mises à jour suivantes)

```bash
./migration/deploy.sh wordpress
```

Vendorise Dompdf en mode production (sans PHPUnit/PHPCS), puis synchronise
uniquement `wp-content/plugins/sierra-logistics-core/` et
`wp-content/themes/sierra-gestion/` - jamais le reste de `gestion/`
(wp-config-local.php, uploads, autres extensions installées directement en
production restent intacts).

## Principe directeur : zéro URL en dur

Toute cette procédure part d'une règle appliquée dans le code (front et
plugin) : aucune URL absolue n'est jamais écrite en dur.

- **Front** : les appels à l'API utilisent un chemin relatif (`/gestion/wp-json/sierra/v1/...`)
  puisque le front et WordPress sont servis par le même domaine. `NEXT_PUBLIC_SITE_URL`
  (métadonnées, sitemap, robots) et `NEXT_PUBLIC_ALLOW_INDEXING` sont les deux
  seules variables d'environnement liées au domaine, lues au build.
- **WordPress** : `WP_HOME` et `WP_SITEURL` viennent du fichier de config non
  versionné (`.env` / `wp-config-local.php`), jamais codés dans `wp-config.php`
  lui-même. Le plugin ne stocke aucune URL absolue en base : toute URL affichée
  (lien de facture dans un e-mail, PDF, etc.) est reconstruite à la volée avec
  `home_url()`, `rest_url()`, `admin_url()` et `wp_upload_dir()`.
- **CORS, expéditeur d'e-mail, coordonnées sur les PDF** : dans les réglages
  du plugin (page "Sierra Logistics > Réglages"), jamais dans le code.

Grâce à ça, changer de domaine ne touche à aucun fichier source : seulement
aux variables d'environnement, aux réglages wp-admin, et à la configuration
d'hébergement.

## Blocage d'indexation pendant la phase sous-domaine temporaire

Un sous-domaine temporaire ne doit pas être indexé par les moteurs de
recherche (sinon le domaine définitif hérite de contenu dupliqué). Deux
dispositifs, indépendants l'un de l'autre :

1. **Front** : balise `<meta name="robots">` posée dans `src/app/layout.js`,
   pilotée par `NEXT_PUBLIC_ALLOW_INDEXING`. Mettre `NEXT_PUBLIC_ALLOW_INDEXING=false`
   dans l'environnement de build du sous-domaine temporaire ; absent ou `true`
   partout ailleurs (y compris en production actuelle sur Vercel, pour ne rien
   changer à son comportement). `src/app/robots.js` et `src/app/sitemap.js`
   suivent le même flag. **Nécessite un rebuild du front pour changer d'état**
   (la métadonnée est injectée au moment du build) : c'est la limite du "sans
   rebuild" pour la partie front tant qu'il n'est pas encore exporté en
   statique.
   Une fois le front passé en export statique sur OVH (étape "Adaptation du
   front React/Next.js"), le dispositif principal devient un bloc dans le
   `.htaccess` à la racine (`Header set X-Robots-Tag "noindex, nofollow"`),
   clairement délimité par des commentaires `SIERRA NOINDEX ... FIN SIERRA
   NOINDEX` : le supprimer (ou le commenter) est une simple modification de
   fichier de configuration via FTP, sans rebuild et sans toucher au code.
2. **WordPress** : réglage natif *Réglages > Lecture > "Demander aux moteurs
   de recherche de ne pas indexer ce site"* (option `blog_public` à `0`). À
   cocher à l'installation sur le sous-domaine temporaire, à décocher au
   moment de la bascule. Aucune ligne de code, effet immédiat.

## Bascule vers le domaine définitif

Procédure à suivre une fois le domaine définitif acheté et prêt à être
rattaché. Prévoir une fenêtre courte (quelques minutes de coupure possible le
temps de la propagation DNS et du `search-replace`).

### 1. Rattacher le domaine dans OVH

Dans l'espace client OVH, sur l'hébergement mutualisé qui héberge déjà le
sous-domaine temporaire : **Multisite** (fonctionnalité d'hébergement OVH qui
permet d'attacher plusieurs domaines au même espace, sans rapport avec le
"WordPress Multisite") → ajouter le nouveau domaine définitif, en le pointant
vers **le même dossier** (`www/`) que le sous-domaine temporaire. Si le
domaine est déjà chez un autre registrar, mettre à jour ses serveurs DNS ou,
a minima, sa zone DNS pour pointer vers l'hébergement OVH, et attendre la
propagation (jusqu'à 24h, généralement bien plus rapide).

Générer ensuite le certificat SSL Let's Encrypt pour le nouveau domaine
depuis l'onglet SSL de l'espace client OVH (gratuit, renouvellement
automatique). Vérifier qu'il est actif (cadenas HTTPS) avant de continuer.

### 2. Mettre à jour les variables d'environnement

- **WordPress** (`wordpress/wp-config-local.php` ou `.env` sur le serveur,
  jamais commité) :
  ```
  WP_HOME=https://votre-domaine-definitif.sn
  WP_SITEURL=https://votre-domaine-definitif.sn/gestion
  ```
- **Front** (variables d'environnement du build, pas dans le code) :
  ```
  NEXT_PUBLIC_SITE_URL=https://votre-domaine-definitif.sn
  NEXT_PUBLIC_ALLOW_INDEXING=true
  ```
  Si une variable d'URL d'API explicite est utilisée plutôt que le chemin
  relatif par défaut (cas d'un front qui ne serait finalement pas sur le même
  domaine que `/gestion/`), la mettre à jour aussi.
- **Réglages du plugin** (wp-admin > Sierra Logistics > Réglages) : origine(s)
  CORS autorisée(s) pour l'API publique `sierra/v1` → remplacer l'origine du
  sous-domaine temporaire par celle du domaine définitif.

Puis **rebuilder le front** si `NEXT_PUBLIC_SITE_URL`/`NEXT_PUBLIC_ALLOW_INDEXING`
ont changé (nécessaire tant que ce sont des variables lues au build - voir
section précédente) et redéployer le dossier `www/` généré.

### 3. Remplacer l'ancienne URL dans la base WordPress

Les tables WordPress contiennent des données sérialisées PHP (widgets,
options de certains plugins) : un remplacement SQL brut (`UPDATE ... SET
option_value = REPLACE(...)`) casse la sérialisation et corrompt ces lignes.
Utiliser impérativement un outil qui sait resérialiser correctement :

- **Avec accès SSH/WP-CLI** (recommandé) :
  ```
  wp search-replace 'https://sous-domaine-temporaire.exemple.sn' 'https://votre-domaine-definitif.sn' --dry-run
  ```
  Vérifier le résumé du dry-run (nombre de lignes/tables concernées), puis
  relancer sans `--dry-run` pour appliquer.
- **Sans SSH** (mutualisé sans accès ligne de commande) : installer
  temporairement le plugin **Better Search Replace** depuis wp-admin, lancer
  la même recherche/remplacement avec son interface (qui propose aussi un
  mode "dry run"), puis désinstaller le plugin une fois la bascule confirmée.

Ne faire cette opération qu'une fois les DNS propagés et le nouveau domaine
accessible en HTTPS, pour pouvoir vérifier immédiatement après coup.

### 4. Rediriger l'ancien sous-domaine vers le nouveau domaine

Dans le `.htaccess` à la racine du sous-domaine temporaire (celui qui servait
`www/`), ajouter une redirection 301 qui conserve le chemin et les
paramètres de requête :

```apache
RewriteEngine On
RewriteCond %{HTTP_HOST} ^sous-domaine-temporaire\.exemple\.sn$ [NC]
RewriteRule ^(.*)$ https://votre-domaine-definitif.sn/$1 [R=301,L,QSA]
```

`QSA` conserve les paramètres (`?id=...`) et `$1` conserve le chemin : un
lien de facture `https://sous-domaine-temporaire.exemple.sn/facture-proforma?id=...`
déjà envoyé à un client continue de fonctionner.

### 5. Configurer SPF/DKIM pour le nouveau domaine

Si les e-mails (confirmation de devis, factures) sont envoyés depuis une
adresse du domaine définitif (`contact@votre-domaine-definitif.sn`), ajouter
dans la zone DNS du nouveau domaine les enregistrements SPF et DKIM fournis
par l'hébergeur ou le service d'envoi utilisé, avant la bascule effective, le
temps que leur propagation soit faite. Sans ça, les e-mails envoyés depuis le
nouveau domaine risquent d'atterrir en spam. Vérifier ensuite avec un
service de test de délivrabilité (ex. mail-tester.com).

### 6. Retirer le blocage d'indexation

- Décocher *Réglages > Lecture > "Demander aux moteurs de recherche de ne pas
  indexer ce site"* dans wp-admin.
- Supprimer (ou commenter) le bloc `SIERRA NOINDEX ... FIN SIERRA NOINDEX`
  dans le `.htaccess` du front, ou redéployer avec `NEXT_PUBLIC_ALLOW_INDEXING=true`
  si le blocage front reposait encore sur la balise meta côté build.
- Soumettre le nouveau sitemap (`https://votre-domaine-definitif.sn/sitemap.xml`)
  à Google Search Console / Bing Webmaster Tools.

### 7. Checklist de vérification post-bascule

- [ ] Demande de devis de bout en bout sur le nouveau domaine (soumission,
      calcul, affichage de la facture proforma).
- [ ] E-mails de confirmation/notification bien reçus, et **pas en spam**
      (vérifier la boîte du client test et celle de l'équipe).
- [ ] Connexion à `https://votre-domaine-definitif.sn/gestion/wp-admin/`
      fonctionnelle pour un compte `sierra_admin` et un compte `sierra_agent`.
- [ ] Génération d'un PDF (proforma et facture définitive) sans erreur, avec
      les bonnes coordonnées d'entreprise.
- [ ] Les anciennes URL du sous-domaine temporaire redirigent bien (301) vers
      l'équivalent sur le domaine définitif, chemin et paramètres compris.
- [ ] Le sous-domaine temporaire n'est plus utilisé dans aucun lien envoyé
      (nouveaux e-mails, réglages CORS, réseaux sociaux le cas échéant).
- [ ] Le site est de nouveau indexable (balise meta, en-tête HTTP, réglage
      WordPress) et le sitemap du nouveau domaine est soumis.
