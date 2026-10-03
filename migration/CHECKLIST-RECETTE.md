# Checklist de recette

À suivre une fois le site installé sur le sous-domaine temporaire (voir
`migration/DEPLOIEMENT.md`), avant de considérer la migration terminée.
Cochez au fur et à mesure ; notez ici tout écart constaté.

## Site public

- [ ] Toutes les pages s'affichent normalement (menu, accueil, services,
      réalisations, pages légales) : design et textes identiques à avant,
      sauf la politique de confidentialité (mention MySQL au lieu de
      Supabase, volontaire).
- [ ] Le menu mobile et les menus déroulants fonctionnent.
- [ ] Les liens "Demander un devis", "Inscrire son camion", "Commander un
      camion", "Suivre sa flotte" mènent aux bonnes pages.

## Demande de devis

- [ ] Remplir le formulaire `/devis` avec des informations réelles (ville de
      départ, ville d'arrivée, type de camion, poids, date) et l'envoyer.
- [ ] La page de facture proforma s'affiche avec un numéro (`PRO-AAAA-NNNNN`),
      le détail du trajet et le bon montant total.
- [ ] Comparer le montant avec un calcul manuel (zone tarifaire x distance x
      coefficient du camion + TVA 18%) : identique au centime.
- [ ] Un e-mail de confirmation arrive à l'adresse indiquée dans le
      formulaire (vérifier aussi les spams).
- [ ] Un e-mail de notification arrive à l'adresse de l'équipe configurée
      dans Sierra Logistics > Réglages.
- [ ] Soumettre le formulaire avec une ville inexistante ou un champ
      manquant : un message d'erreur clair apparaît, rien n'est enregistré.

## Retrouver un devis

- [ ] Sur `/commander`, saisir le numéro de téléphone utilisé plus haut : la
      même facture proforma s'affiche.
- [ ] Saisir un numéro qui n'existe pas : message "Aucun devis trouvé",
      pas d'erreur technique affichée.

## Inscription d'un camion

- [ ] Remplir et envoyer le formulaire `/inscription-camion`.
- [ ] Le véhicule apparaît dans wp-admin > Sierra Logistics > Véhicules,
      avec le statut "Disponible".

## Connexion à l'espace de gestion

- [ ] `https://.../gestion/wp-admin/` demande une connexion.
- [ ] Un compte **Admin Sierra Logistics** voit tout le menu Sierra
      Logistics (Tableau de bord, Devis, Commandes, Véhicules, Réglages,
      Import) et aucun menu WordPress standard (Articles, Pages, Extensions...).
- [ ] Un compte **Agent Sierra Logistics** voit Tableau de bord, Devis,
      Commandes, mais **pas** Véhicules, Réglages, ni Import.
- [ ] Un agent qui essaie d'accéder directement à l'URL de Réglages ou
      Véhicules reçoit un message "Accès refusé", pas une erreur serveur.
- [ ] Après connexion, redirection automatique vers le tableau de bord
      Sierra (pas le tableau de bord WordPress par défaut).

## Gestion des devis

- [ ] Le tableau de bord affiche des chiffres cohérents (devis reçus, en
      attente, commandes validées, chiffre d'affaires).
- [ ] La liste des devis permet de filtrer par statut et par date, et de
      rechercher par nom/téléphone/ville.
- [ ] Ouvrir la fiche d'un devis : tous les champs sont corrects, l'historique
      affiche au moins la création.
- [ ] "Recalculer le devis" sans rien changer donne le même total (sauf si
      les réglages tarifaires ont changé depuis).
- [ ] Changer manuellement le statut d'un devis : l'historique enregistre le
      changement avec la date et l'ancien/nouveau statut.

## Validation en commande

- [ ] Sur un devis "en attente", cliquer "Valider en commande".
- [ ] Un véhicule disponible du bon type est proposé en premier dans la
      liste déroulante.
- [ ] Après validation : le devis passe au statut "Commandé", une commande
      apparaît dans Sierra Logistics > Commandes avec le camion et le
      chauffeur, le véhicule passe à "En course" dans Véhicules.
- [ ] Essayer de valider une seconde fois le même devis : message d'erreur
      clair ("déjà validé"), pas de doublon créé.
- [ ] S'il n'y a aucun véhicule disponible : message clair, pas d'erreur
      technique.

## Facturation

- [ ] Depuis la fiche d'un devis, "Télécharger le PDF" produit un PDF
      correct (en-tête avec le nom/adresse/téléphone de l'entreprise, détail
      du trajet, montants, numéro de facture).
- [ ] Pour un devis validé, le PDF affiche "FACTURE DÉFINITIVE" avec le
      véhicule et le chauffeur ; pour un devis non validé, "FACTURE PROFORMA".
- [ ] "Envoyer la facture PDF par e-mail" : l'e-mail arrive avec le PDF en
      pièce jointe, pas juste un lien.
- [ ] "Exporter en CSV" (depuis la liste des devis) télécharge un fichier
      CSV lisible dans Excel/LibreOffice, avec toutes les lignes.

## Véhicules (admin uniquement)

- [ ] Ajouter un véhicule manuellement depuis wp-admin.
- [ ] Modifier un véhicule existant (statut, téléphone...).
- [ ] Supprimer un véhicule de test.

## Réglages (admin uniquement)

- [ ] Modifier le tarif d'une zone (ex. Zone 1), enregistrer, puis créer un
      nouveau devis dans cette zone : le nouveau tarif est bien appliqué.
      Remettre la valeur d'origine ensuite si c'était un test.
- [ ] Modifier les coordonnées de l'entreprise, puis télécharger un PDF :
      les nouvelles coordonnées apparaissent.

## Import des données historiques

- [ ] Le nombre de devis dans Sierra Logistics > Devis correspond au nombre
      de lignes de l'export Supabase (`quotes.csv`), plus les devis créés
      pendant la recette.
- [ ] Un ancien devis (importé) a un numéro de facture valide et affiche
      les bons montants.
- [ ] Les anciens comptes admins ont reçu l'e-mail de définition de mot de
      passe et peuvent se connecter une fois leur mot de passe choisi.

## Mobile

- [ ] Le site public s'affiche correctement sur un téléphone (menu, formulaires).
- [ ] L'espace de gestion reste utilisable sur tablette (menu latéral qui
      se replie, tableaux lisibles).
