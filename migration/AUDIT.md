# Audit pré-migration : Sierra Logistics (Supabase → WordPress/MySQL)

Date de l'audit : 2026-10-02. Portée : code du dépôt `sierra-logistics-web` sur `main` (commit `5fd3b99`) et projet Supabase associé (lecture seule, via les migrations SQL du dépôt et une lecture des compteurs de lignes).

## 1. Stack du front

- **Framework** : Next.js 16.3.2, App Router (dossier `src/app`), React 19.2.8, build/dev via Turbopack.
- **Pas de Vite, pas de SPA classique.** C'est un point important à valider avec vous avant de bâtir le plan (voir section 10) : le cahier des charges décrit un front "compilé en `dist/`, `VITE_API_URL`, `.htaccess` pour SPA" — ce n'est pas la forme actuelle du projet. Next.js a des server components, des routes API (`src/app/api/**/route.js`) et du rendu serveur, ce qu'un hébergement mutualisé OVH sans Node ne peut pas exécuter tel quel.
- **Style** : Tailwind CSS v4, composants shadcn/radix-ui (`src/components/ui/*`).
- **Dépendances clés** : `@supabase/supabase-js` (client + admin), `resend` (envoi d'e-mails transactionnels), `next-themes`, `sonner`, `lucide-react`.
- **Variables d'environnement** (`.env.example`) :
  - `NEXT_PUBLIC_SUPABASE_URL`, `NEXT_PUBLIC_SUPABASE_ANON_KEY` — exposées au navigateur, utilisées par `src/lib/supabaseClient.js`.
  - `SUPABASE_SERVICE_ROLE_KEY` — serveur uniquement, utilisée par `src/lib/supabaseAdmin.js` pour contourner le RLS.
  - `RESEND_API_KEY`, `RESEND_FROM_EMAIL` — serveur uniquement, pour l'envoi d'e-mail depuis l'admin.
- **Déploiement actuel** : Vercel (projet `sierra-logistics-web`), confirmé par `.vercel/project.json`. Aucun `vercel.json` personnalisé, aucun middleware Next.

## 2. Appels Supabase

### 2.1 Site public (à remplacer par l'API REST WordPress)

| Fichier:ligne | Table | Opération | Détail |
|---|---|---|---|
| `src/app/(site)/devis/devis-form.jsx:69-96` | `quotes` | INSERT (anon) | Calcule le devis **côté client** (`calculerDevis`, voir §4) puis insère directement la ligne complète, montants compris, avec la clé anon. Aucune route serveur n'est impliquée. |
| `src/app/(site)/commander/commander-form.jsx:29-34` | `quotes` | SELECT (anon) | Recherche par `telephone`, trie par `created_at desc`, prend la première ligne. La policy RLS anon sur `quotes` est `using (true)` (lecture non filtrée au niveau base, voir §2.3) : le filtre par téléphone est fait côté client JS, pas par la base. |
| `src/app/(site)/inscription-camion/inscription-camion-form.jsx:43-52` | `vehicles` | INSERT (anon) | Statut forcé à `disponible` ou `maintenance` selon le formulaire (pas de validation serveur). |
| `src/app/facture-proforma/facture-proforma-view.jsx:25-29` | `quotes` | SELECT (anon) | Lit un devis par `id` (passé en query string). Page accessible sans authentification à quiconque connaît l'UUID. |
| `src/app/facture-proforma/facture-proforma-view.jsx:38-45` | — (via `/api/commandes`) | GET | Appelle la route API `GET /api/commandes?proformaId=`, qui lit `commandes` avec la clé service_role (§2.3). |
| `src/app/facture-proforma/facture-proforma-view.jsx:55-59` | — (via `/api/commandes/valider`) | POST | Bouton "Valider la commande", visible et actionnable par **n'importe quel visiteur** sur la page facture-proforma — aucune vérification d'authentification. Voir §2.3 et §10. |

### 2.2 Espace de gestion (à retirer du front, à refaire dans wp-admin)

| Fichier:ligne | Table | Opération | Détail |
|---|---|---|---|
| `src/lib/useAdminSession.js:14-33` | `admins` + Supabase Auth | SELECT (authenticated) | Combine session Auth + présence dans `admins` pour déterminer `isAdmin`. |
| `src/app/admin/login/page.jsx:30-52` | Supabase Auth + `admins` | `signInWithPassword`, SELECT | Connexion email/mot de passe, puis vérification d'appartenance à `admins`. |
| `src/app/admin/(dashboard)/page.jsx:16-20` | `quotes`, `vehicles`, `commandes` | SELECT (authenticated) | Tableau de bord : tous les devis, tous les véhicules, toutes les commandes, agrégés côté client. |
| `src/app/admin/(dashboard)/factures/page.jsx:15-19` | `quotes` | SELECT (authenticated) | Liste complète, filtrage/recherche fait en mémoire côté client (pas de pagination côté base). |
| `src/app/admin/(dashboard)/factures/[id]/page.jsx:22-38` | `quotes`, `commandes` | SELECT (authenticated) | Détail d'un devis + commande associée. |
| `src/app/admin/(dashboard)/factures/[id]/page.jsx:50-56` | — (via `/api/admin/send-invoice-email`) | POST | Envoi de l'e-mail de facture, avec jeton Supabase Auth en Bearer. |
| `src/app/admin/(dashboard)/commandes/page.jsx:12-26` | `commandes`, `quotes` | SELECT (authenticated) | Liste des commandes, jointure faite manuellement côté client (deux requêtes + `Map`). |
| `src/components/admin/admin-shell.jsx:23` | Supabase Auth | `signOut` | Déconnexion. |
| `src/app/api/commandes/route.js:14-18` | `commandes` | SELECT (service_role) | Utilisée par la vue publique facture-proforma en mode définitif — donc techniquement appelée depuis le site public, mais le code et les données (chauffeur, téléphone) relèvent de la gestion. |
| `src/app/api/commandes/valider/route.js` | `quotes`, `vehicles`, `commandes` | SELECT/UPDATE/INSERT (service_role) | Logique de validation d'une commande : réserve un véhicule disponible (priorité au type demandé), crée la ligne `commandes`, passe le devis à `statut = 'commandé'`. Voir §2.3 pour l'absence d'authentification. |
| `src/app/api/admin/send-invoice-email/route.js` | `admins`, `quotes` | SELECT (service_role) + Supabase Auth | Vérifie le jeton Bearer via `auth.getUser`, vérifie l'appartenance à `admins`, lit le devis, envoie l'e-mail via Resend. |

Aucune page de gestion des véhicules (CRUD) n'existe dans le front actuel : les véhicules ne sont créés que via le formulaire public `/inscription-camion`, jamais modifiés ni supprimés depuis l'admin.

### 2.3 Auth, RLS, et autres usages Supabase

Pas de Storage, pas d'Edge Functions, pas de RPC Postgres, pas de realtime : vérifié par grep (`rpc(`, `channel(`, `postgres_changes`, `storage.from`, `functions.invoke`) sans résultat, et par l'API Storage du projet (`GET /storage/v1/bucket` → `[]`).

Les règles d'accès vivent dans `supabase/migrations/*.sql` (3 fichiers, lus intégralement) :
- `vehicles` : anon peut `INSERT` (policy "anon can register a vehicle" + grant). Lecture réservée aux `authenticated` présents dans `admins`.
- `quotes` : anon peut `INSERT` et `SELECT` **sans restriction** (`using (true)`) — la policy ne filtre pas par téléphone ou par id, elle autorise la lecture de toute la table à la clé anon. Le filtrage visible dans l'app (par id ou par téléphone) est une convention du code, pas une garantie de la base.
- `commandes` : aucun grant pour `anon`. Accessible uniquement via les deux routes API, qui utilisent `SUPABASE_SERVICE_ROLE_KEY` (contourne RLS).
- `admins` : les colonnes `role` (`admin`/`agent`) existent et sont contraintes par un `check`, mais **aucune policy ni aucun code applicatif ne distingue les deux rôles** — un `agent` a exactement les mêmes droits qu'un `admin` partout où j'ai cherché (`grep role` dans `src/` : aucun résultat). Voir §7 et §10.
- Aucune authentification sur `POST /api/commandes/valider` : n'importe qui connaissant un `quoteId` (ou le devinant/l'énumérant, puisque `quotes` est lisible par tous) peut déclencher la réservation d'un camion et faire passer un devis en "commandé". C'est le comportement actuel, pas une préconisation — à garder tel quel ou corriger est une décision produit à prendre (§10).

### 2.4 Volumes de données actuels (lecture seule, via le projet Supabase live)

`vehicles`: 0 ligne · `quotes`: 2 lignes · `commandes`: 0 ligne · `admins`: 1 ligne.

Le projet est donc essentiellement vide à ce jour — pas de volume réel à migrer. Cela change la donne sur l'urgence et le risque de la bascule : peu de chose à perdre, la fenêtre de vérification croisée (§ Déploiement du cahier des charges) peut être courte.

## 3. Logique de calcul des devis

Fichier source : `src/lib/pricing.js`, appelée uniquement depuis `src/app/(site)/devis/devis-form.jsx:63-67`, **au moment de la soumission du formulaire, côté client (navigateur)**. Le serveur ne recalcule jamais rien aujourd'hui : le visiteur envoie directement les montants calculés dans son navigateur à Supabase via la clé anon.

Étapes et formules exactes :

1. **Distance** (`calculerDistance`, lignes 29-41) : différence absolue entre la `distance` (km depuis Dakar, valeur fixe par ville) de la ville de départ et celle de la ville d'arrivée, dans `src/data/villes-senegal.json` (`departmental_capitals`, 44 villes, noms normalisés sans accent/casse pour la comparaison). Ce n'est **pas** une distance routière réelle, c'est une approximation par différence de "distance à Dakar".
2. **Zone tarifaire** (`determinerZone`, lignes 43-48, constante `ZONES_TARIFAIRES` lignes 3-9) :
   - 1-100 km → 500 FCFA/km (Zone 1)
   - 101-300 km → 700 FCFA/km (Zone 2)
   - 301-500 km → 800 FCFA/km (Zone 3)
   - 501-750 km → 900 FCFA/km (Zone 4)
   - 751 km et plus → 1000 FCFA/km (Zone 5)
3. **Coefficient camion** (constante `COEFFICIENTS_CAMION` lignes 11-18) : Camion benne 1.0, Camion bâché 1.2, Plateau 1.3, Fourgon 1.1, Camion citerne 1.5, Camion frigorifique 1.7. Défaut 1.0 si type inconnu.
4. **Montant transport** = `tarif_zone × distance`.
5. **Majoration** = `(coefficient - 1) × tarif_zone × distance`.
6. **Sous-total** = montant transport + majoration.
7. **TVA** = `sous_total × 18%` (taux fixe, codé en dur).
8. **Total** = sous-total + TVA.
9. Tous les montants sont arrondis à l'entier (`Math.round`) avant stockage.

Les listes de villes, zones, coefficients et taux de TVA sont aujourd'hui des constantes dans le code (pas de table de réglages) — à porter dans la page de réglages de l'admin WordPress comme demandé dans le cahier des charges.

## 4. Ce que le visiteur voit après l'envoi du formulaire de devis

Redirection immédiate vers `/facture-proforma?id=<uuid du devis>` (`devis-form.jsx:100`), qui affiche l'intégralité du détail tarifaire (transport, majoration, sous-total, TVA, total) et un bouton "Imprimer" (impression navigateur, pas de PDF généré côté serveur). **Aucun e-mail n'est envoyé automatiquement** à la soumission — l'envoi d'e-mail n'existe que comme action manuelle de l'admin (§5), déclenchée depuis `/admin/factures/[id]`.

## 5. Génération de documents, numérotation, e-mails

- **Pas de PDF.** La "facture" est une page HTML imprimable (`src/app/facture-proforma/facture-proforma-view.jsx`), avec un bouton `window.print()`. Il n'y a aucune librairie de génération PDF dans le projet actuel.
- **Pas de numérotation séquentielle.** Le "N°" affiché (`quote.id?.split("-")[0]`) est simplement le premier segment de l'UUID du devis — ni séquentiel, ni garanti sans trou, ni lisible.
- **Facture "définitive"** : même composant que la proforma, avec `?definitive=true`, qui affiche en plus le véhicule/chauffeur assigné (`src/app/facture-proforma/facture-proforma-view.jsx:209-236`). `src/app/facture-definitive/page.jsx` n'est qu'une redirection vers cette vue (l'ancienne page `facture-definitive.html` lisait une table `factures` qui n'était jamais alimentée — commentaire explicite dans le code, lignes 8-12).
- **E-mail** : uniquement déclenché manuellement par un admin connecté, via `POST /api/admin/send-invoice-email` (Resend, pas wp_mail). Contenu : lien vers la facture + récapitulatif court (marchandise, poids, total). Pas de pièce jointe PDF (puisqu'aucun PDF n'existe). Expéditeur par défaut `onboarding@resend.dev` si `RESEND_FROM_EMAIL` n'est pas configuré.
- **Partage alternatif** : un lien WhatsApp pré-rempli est généré côté admin (`src/lib/whatsapp.js` + `src/app/admin/(dashboard)/factures/[id]/page.jsx:187-195`), pas d'envoi automatique.
- **Pas d'export CSV** existant dans l'admin actuel.

Le cahier des charges demande un vrai PDF (Dompdf), une numérotation séquentielle sans trou et un export CSV : ce sont des nouveautés par rapport à l'existant, pas une parité à conserver à l'identique.

## 6. Rôles et droits

Table `admins` : colonne `role` avec contrainte `check (role in ('admin', 'agent'))`, défaut `'agent'`. **Mais dans tout le code applicatif et toutes les policies RLS, un agent a exactement les mêmes droits qu'un admin** — aucune vérification de `role` nulle part (`grep -rn "\.role\|role ===" src` ne retourne rien). La distinction existe dans le schéma mais n'est pas appliquée. Voir question en §10 : faut-il introduire une vraie distinction de droits dans WordPress (`sierra_admin` vs `sierra_agent`), ou répliquer l'absence de distinction actuelle ?

Il n'existe pas de page de gestion des comptes admin dans le front (création/suppression d'admin se fait à la main via le dashboard Supabase, cf. commentaire dans `supabase/migrations/20260827090000_admin_access.sql:33-37`).

## 7. Valeurs de statut

- `quotes.statut` : `'en_attente'` (défaut) ou `'commandé'`. Contrainte CHECK en base, seulement ces deux valeurs. Pas de statut "refusé"/"annulé"/"facturé" — le cahier des charges WordPress pourra en ajouter si besoin (à valider avec vous).
- `vehicles.status` : `'disponible'` (défaut), `'en_course'`, `'maintenance'`. `en_course` n'est jamais choisi manuellement (pas dans `STATUS_OPTIONS` du formulaire public) — il est posé automatiquement par `POST /api/commandes/valider` au moment de la réservation, et n'est jamais repassé à `disponible` par le code actuel (pas d'écran "terminer la course").

## 8. Routes et pages du front

**Site public** (groupe `(site)`, toutes statiques sauf mention) :
`/`, `/fiabilite`, `/technologie`, `/support-client`, `/transport-routier`, `/transport-conteneurs`, `/location-camions`, `/bennes-tp`, `/entreposage`, `/distribution-locale`, `/transporteurs`, `/suivi-flotte` (marketing statique, aucune donnée réelle malgré le nom), `/realisations/transfert-cereales`, `/realisations/empotage-ble`, `/realisations/manutention-ter`, `/realisations/transport-arachide`, `/politique-de-confidentialite`, `/termes-et-conditions`.

**Formulaires publics (écrivent/lisent Supabase)** : `/devis`, `/commander`, `/inscription-camion`.

**Factures (publiques par lien, exclues du sitemap/robots)** : `/facture-proforma?id=...`, `/facture-proforma?id=...&definitive=true`, `/facture-definitive?id=...` (redirige vers la précédente).

**Redirection** : `/expedier` → `/devis` (301, définie dans `next.config.js`, ancienne page consolidée).

**Admin (protégé côté client uniquement, pas de middleware serveur)** : `/admin` (dashboard), `/admin/login`, `/admin/factures`, `/admin/factures/[id]`, `/admin/commandes`. Pas de page de gestion des véhicules. La protection repose sur `useAdminSession` + redirection client (`src/app/admin/(dashboard)/layout.js:13-17`) — le HTML de la coquille se charge avant la redirection, mais aucune donnée sensible n'est livrée avant l'authentification Supabase.

**Routes API** : `GET/POST /api/commandes`, `POST /api/commandes/valider`, `POST /api/admin/send-invoice-email`.

**Next spécifique** : `sitemap.xml` (liste 12 routes en dur, `src/app/sitemap.js`, domaine placeholder `sierra-logistics.example.com` — à corriger au passage), `robots.txt` (exclut `/facture-proforma` et `/facture-definitive`, même domaine placeholder).

## 9. Politique de confidentialité

`src/app/(site)/politique-de-confidentialite/page.js:82-89` mentionne explicitement "Supabase (PostgreSQL)" et "Row Level Security" — ce texte devra être mis à jour pour refléter MySQL/WordPress une fois la bascule faite (changement de texte nécessaire, pas accessoire : voir règle "ne change pas les textes sauf nécessité validée").

## 10. Ambigu, manquant, ou à valider avant de passer au plan

1. **Le front n'est pas une SPA Vite avec `dist/` statique, c'est du Next.js avec rendu serveur et routes API.** Le cahier des charges suppose un export statique classique. Il faut qu'on choisisse ensemble comment déployer ce front sur un mutualisé OVH sans Node : soit (a) un export statique Next (`output: "export"`) en adaptant tout ce qui dépend du serveur (les 3 routes API, aujourd'hui indispensables à `/devis`, `/commander` via lecture, et à la validation de commande), soit (b) garder le front sur Vercel (comme aujourd'hui) et ne basculer que la donnée + la gestion vers OVH/WordPress, soit (c) une autre cible. C'est une décision structurante, à trancher avant le plan détaillé.
2. **Fichier `migration/sierra_schema_mysql.sql` et `migration/convert_csv_to_mysql.py` cités comme "déjà convertis"** : ils n'existent pas dans ce dépôt (`migration/` était un dossier vide avant cet audit). Je pars du schéma Supabase réel ci-dessus (3 migrations SQL lues intégralement) ; dites-moi si ces fichiers existent ailleurs (autre dépôt, pièce jointe) ou si je dois les produire moi-même en partie 1.
3. **Distinction `admin`/`agent`** : aujourd'hui sans effet (§6). Voulez-vous une vraie séparation de droits dans WordPress (et si oui laquelle : accès aux réglages tarifaires ? à l'export ? à la suppression de véhicules ?), ou une réplique fidèle de l'absence de distinction actuelle ?
4. **`POST /api/commandes/valider` est public et non authentifié aujourd'hui** (§2.1, §2.3) : n'importe qui avec un `quoteId` peut valider une commande et réserver un camion. À garder identique (parité stricte) ou à corriger (réserver l'action aux agents connectés) dans la nouvelle API publique `sierra/v1` ? Le cahier des charges demande une API publique "réduite au strict nécessaire" pour `POST /quotes`, ce qui suggère de ne pas reproduire cette route en public — à confirmer.
5. **Lecture publique non filtrée de `quotes`** (policy `using (true)`) : la recherche par téléphone sur `/commander` fonctionne parce que le filtre est fait côté client, pas parce que la base protège les autres lignes. Je pars du principe que la nouvelle route GET publique (si vous en voulez une pour "retrouver mon devis par téléphone") doit filtrer côté serveur WordPress et ne jamais exposer la table entière. Confirmez.
6. **Calcul de distance approximatif** (différence de distance à Dakar, pas un itinéraire réel, §3) : je porte la formule à l'identique comme demandé, mais vouliez-vous en profiter pour la revoir, ou strictement conserver le comportement actuel pour l'instant ?
7. **Emails de confirmation/notification automatiques** demandés dans le cahier des charges pour `POST /quotes` : n'existent pas aujourd'hui (§4). Contenu/destinataires exacts à définir (qui reçoit la notification équipe ? quelle adresse ?).
8. **Génération de PDF, numérotation séquentielle, export CSV** : nouveautés, pas de parité à respecter (§5). Format exact de la numérotation à définir (ex. `PRO-2026-00001` ?) et coordonnées de l'entreprise pour l'en-tête des PDF (adresse complète, SIRET/NINEA, téléphone, logo — j'ai trouvé `+221 77 143 71 71` et "Diamniadio, Sénégal" dans le modèle d'e-mail actuel, à confirmer/compléter).
9. **Fichiers Supabase Storage** : aucun bucket trouvé (§2.3), donc rien à rapatrier sur ce point — sauf si des fichiers sont gérés ailleurs (hors Supabase) que je n'aurais pas vu.
10. **Admin unique actuel (1 ligne dans `admins`)** : son e-mail et son rôle exact à me fournir pour l'import, ou à définir pour le nouveau compte WordPress.
11. **Identifiants et informations d'hébergement OVH** (nom de la base MySQL à créer, utilisateur, serveur `xxx.mysql.db`, accès SFTP) : à me fournir le moment venu, je ne les invente pas.
12. **Textes légaux** (`politique-de-confidentialite`, `termes-et-conditions`) : la mention Supabase (§9) devra changer. Dites-moi si d'autres passages de ces pages doivent être revus pendant la bascule, ou si on se limite au strict nécessaire.

---

Je m'arrête ici comme convenu : je ne commence pas la partie 1 (plugin WordPress) tant que ce document n'est pas validé et que les points ci-dessus n'ont pas de réponse, en particulier le point 1 qui conditionne toute l'architecture de déploiement.
