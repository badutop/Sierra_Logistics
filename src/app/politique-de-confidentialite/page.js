import { PageHero } from "@/components/page-hero";
import { BackHomeLink } from "@/components/back-home-link";

export const metadata = {
  title: "Politique de Confidentialité",
  description:
    "Politique de confidentialité de Sierra Logistics : données collectées, finalités, conservation et droits des personnes concernées.",
};

export default function PolitiqueConfidentialitePage() {
  return (
    <>
      <PageHero
        title="Politique de Confidentialité"
        image="/images/feature-image-2.png"
      />
      <section className="mx-auto w-[90%] max-w-3xl py-16 text-left">
        <BackHomeLink />
        <div className="space-y-6 text-foreground [&_h2]:mt-8 [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:text-primary [&_li]:ml-5 [&_li]:list-disc [&_p]:leading-relaxed [&_ul]:space-y-2">
          <p className="text-sm text-muted-foreground">
            Dernière mise à jour : 26 août 2026
          </p>

          <p>
            Sierra Logistics (&laquo;&nbsp;nous&nbsp;&raquo;, &laquo;&nbsp;notre&nbsp;&raquo;),
            société de transport et de logistique basée à Diamniadio, Sénégal,
            accorde une grande importance à la protection des données personnelles
            de ses clients, transporteurs partenaires et visiteurs de son site web.
            Cette politique explique quelles données nous collectons, pourquoi, et
            comment vous pouvez exercer vos droits.
          </p>

          <h2>1. Quelles données collectons-nous ?</h2>
          <p>
            Nous collectons uniquement les données que vous nous fournissez
            volontairement en utilisant nos formulaires en ligne :
          </p>
          <ul>
            <li>
              <strong>Demande de devis :</strong> nom, adresse email, numéro de
              téléphone, villes de départ et d&apos;arrivée, type de marchandise,
              poids, type de camion souhaité, date d&apos;expédition et
              informations complémentaires que vous choisissez de renseigner.
            </li>
            <li>
              <strong>Inscription d&apos;un camion (réseau de transporteurs) :</strong>{" "}
              nom du propriétaire, modèle du véhicule, immatriculation, type de
              carburant et numéro de téléphone de contact.
            </li>
            <li>
              <strong>Suivi de commande :</strong> lorsqu&apos;une commande est
              confirmée, le nom et le numéro de téléphone du chauffeur ainsi que
              l&apos;immatriculation du camion assigné sont associés à votre devis
              afin de générer votre facture proforma et votre facture définitive.
            </li>
          </ul>
          <p>
            Nous ne demandons aucune donnée bancaire, aucune pièce
            d&apos;identité et n&apos;utilisons aucun système de dépôt de fichiers
            sur ce site.
          </p>

          <h2>2. Cookies et outils de suivi</h2>
          <p>
            À ce jour, notre site n&apos;utilise ni cookies, ni outil
            d&apos;analyse d&apos;audience, ni traceur publicitaire. Si cela
            venait à changer, cette politique serait mise à jour en conséquence
            et, le cas échéant, un dispositif de recueil de votre consentement
            serait mis en place.
          </p>

          <h2>3. Pourquoi utilisons-nous vos données ?</h2>
          <ul>
            <li>Répondre à vos demandes de devis et vous transmettre une proposition tarifaire ;</li>
            <li>Traiter et suivre vos commandes de transport jusqu&apos;à la livraison ;</li>
            <li>Générer vos factures proforma et définitives ;</li>
            <li>Évaluer et gérer les candidatures de transporteurs partenaires ;</li>
            <li>Vous contacter au sujet d&apos;une demande en cours ;</li>
            <li>Assurer la sécurité et le bon fonctionnement de nos services.</li>
          </ul>

          <h2>4. Où sont stockées vos données ?</h2>
          <p>
            Vos données sont stockées dans une base de données Supabase
            (PostgreSQL), protégée par des règles d&apos;accès (Row Level
            Security) et transmises de manière chiffrée (HTTPS). L&apos;accès aux
            informations sensibles, telles que l&apos;attribution d&apos;un
            chauffeur à une commande, est restreint à nos systèmes internes.
          </p>

          <h2>5. Partage de vos données</h2>
          <p>
            Nous ne vendons ni ne louons vos données personnelles à des tiers.
            Vos informations peuvent être partagées uniquement :
          </p>
          <ul>
            <li>
              Avec le transporteur partenaire chargé d&apos;exécuter votre
              commande, dans la stricte mesure nécessaire à la livraison ;
            </li>
            <li>Avec nos prestataires techniques (hébergement, base de données), tenus à la confidentialité ;</li>
            <li>Si la loi ou une autorité compétente sénégalaise nous y oblige.</li>
          </ul>

          <h2>6. Durée de conservation</h2>
          <p>
            Vos données sont conservées le temps nécessaire à la gestion de votre
            demande ou de votre relation commerciale avec Sierra Logistics, puis
            archivées ou supprimées conformément à nos obligations comptables et
            légales.
          </p>

          <h2>7. Vos droits</h2>
          <p>
            Conformément à la loi sénégalaise n° 2008-12 du 25 janvier 2008 sur
            la protection des données à caractère personnel, vous disposez d&apos;un
            droit d&apos;accès, de rectification, d&apos;opposition et de
            suppression de vos données. Pour exercer ces droits, contactez-nous à{" "}
            <a href="mailto:contact@sierra-logistics.com" className="text-primary underline">
              contact@sierra-logistics.com
            </a>{" "}
            ou au +221 77 143 71 71. Vous pouvez également adresser une réclamation
            à la Commission de Protection des Données Personnelles (CDP) du
            Sénégal.
          </p>

          <h2>8. Modifications de cette politique</h2>
          <p>
            Cette politique de confidentialité peut être mise à jour
            périodiquement pour refléter l&apos;évolution de nos services ou de
            la réglementation applicable. La date de dernière mise à jour figure
            en haut de cette page.
          </p>

          <h2>9. Contact</h2>
          <p>
            Pour toute question relative à cette politique ou au traitement de
            vos données, contactez Sierra Logistics à{" "}
            <a href="mailto:contact@sierra-logistics.com" className="text-primary underline">
              contact@sierra-logistics.com
            </a>{" "}
            — Diamniadio, Sénégal.
          </p>
        </div>
      </section>
    </>
  );
}
