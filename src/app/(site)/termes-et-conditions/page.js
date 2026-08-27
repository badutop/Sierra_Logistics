import { PageHero } from "@/components/page-hero";
import { BackHomeLink } from "@/components/back-home-link";

export const metadata = {
  title: "Termes et Conditions",
  description:
    "Termes et conditions d'utilisation du site et des services en ligne de Sierra Logistics.",
};

export default function TermesConditionsPage() {
  return (
    <>
      <PageHero title="Termes et Conditions" image="/images/feature-image-1.png" />
      <section className="mx-auto w-[90%] max-w-3xl py-16 text-left">
        <BackHomeLink />
        <div className="space-y-6 text-foreground [&_h2]:mt-8 [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:text-primary [&_li]:ml-5 [&_li]:list-disc [&_p]:leading-relaxed [&_ul]:space-y-2">
          <p className="text-sm text-muted-foreground">
            Dernière mise à jour : 26 août 2026
          </p>

          <p>
            Les présents termes et conditions régissent l&apos;utilisation du
            site web de Sierra Logistics, société de transport et de logistique
            basée à Diamniadio, Sénégal, ainsi que l&apos;utilisation de ses
            services en ligne (demande de devis, inscription de transporteurs,
            suivi de commande). En utilisant ce site, vous acceptez les
            présentes conditions.
          </p>

          <h2>1. Nos services</h2>
          <p>Sierra Logistics propose des services de :</p>
          <ul>
            <li>Transport routier de marchandises générales ;</li>
            <li>Transport de conteneurs maritimes (standards et frigorifiques) ;</li>
            <li>Location de camions, avec ou sans chauffeur ;</li>
            <li>Approvisionnement de chantier par bennes TP ;</li>
            <li>Distribution locale et entreposage ;</li>
            <li>Mise en réseau de transporteurs partenaires.</li>
          </ul>
          <p>
            Ce site permet également de demander un devis en ligne, d&apos;inscrire
            un camion au sein de notre réseau de transporteurs partenaires, et
            de suivre l&apos;état d&apos;une commande via une facture proforma
            puis définitive.
          </p>

          <h2>2. Demandes de devis</h2>
          <p>
            Toute demande de devis soumise via ce site est une demande
            d&apos;information et ne constitue pas un engagement contractuel
            ferme de la part de Sierra Logistics. Le devis transmis en réponse
            est établi sur la base des informations fournies et reste indicatif
            jusqu&apos;à confirmation de la commande et émission d&apos;une
            facture définitive. Les tarifs peuvent varier selon les conditions
            réelles d&apos;exécution (poids, distance, disponibilité des
            véhicules, conditions de circulation, etc.).
          </p>

          <h2>3. Inscription au réseau de transporteurs</h2>
          <p>
            L&apos;inscription d&apos;un camion via notre formulaire ne garantit
            pas son intégration automatique à notre réseau de transporteurs
            partenaires. Chaque dossier est évalué selon nos critères de
            sécurité, de fiabilité et de conformité du véhicule. Sierra
            Logistics se réserve le droit d&apos;accepter ou de refuser une
            candidature, sans obligation de justification.
          </p>

          <h2>4. Exactitude des informations</h2>
          <p>
            Vous vous engagez à fournir des informations exactes et à jour lors
            de l&apos;utilisation de nos formulaires (coordonnées, détails de
            marchandise, informations sur le véhicule). Sierra Logistics ne
            saurait être tenue responsable des conséquences d&apos;informations
            erronées ou incomplètes fournies par l&apos;utilisateur.
          </p>

          <h2>5. Responsabilité</h2>
          <p>
            Sierra Logistics met tout en œuvre pour assurer la fiabilité et la
            ponctualité de ses services de transport. Toutefois, notre
            responsabilité ne saurait être engagée en cas de retard ou
            d&apos;incident résultant d&apos;un cas de force majeure, de
            conditions climatiques, de restrictions douanières ou
            administratives, de l&apos;état des infrastructures routières, ou de
            tout autre événement échappant à notre contrôle raisonnable. Les
            conditions particulières de transport (assurance des marchandises,
            délais, responsabilités) font l&apos;objet d&apos;un accord
            spécifique lors de la confirmation de chaque commande.
          </p>

          <h2>6. Propriété intellectuelle</h2>
          <p>
            L&apos;ensemble des contenus présents sur ce site (textes, images,
            logos, mise en page) est la propriété de Sierra Logistics ou de ses
            partenaires, sauf mention contraire. Toute reproduction, totale ou
            partielle, sans autorisation préalable est interdite.
          </p>

          <h2>7. Utilisation du site</h2>
          <p>
            Vous vous engagez à utiliser ce site conformément à sa destination
            et à ne pas porter atteinte à son bon fonctionnement, à sa sécurité,
            ou aux droits de Sierra Logistics et des tiers.
          </p>

          <h2>8. Droit applicable et juridiction</h2>
          <p>
            Les présents termes et conditions sont régis par le droit
            sénégalais. Tout litige relatif à leur interprétation ou à leur
            exécution relève de la compétence exclusive des tribunaux
            sénégalais.
          </p>

          <h2>9. Modifications</h2>
          <p>
            Sierra Logistics se réserve le droit de modifier les présents
            termes et conditions à tout moment. La version en vigueur est celle
            publiée sur cette page, avec sa date de dernière mise à jour.
          </p>

          <h2>10. Contact</h2>
          <p>
            Pour toute question relative à ces termes et conditions,
            contactez-nous à{" "}
            <a href="mailto:contact@sierra-logistics.com" className="text-primary underline">
              contact@sierra-logistics.com
            </a>{" "}
            ou au +221 77 143 71 71 — Diamniadio, Sénégal.
          </p>
        </div>
      </section>
    </>
  );
}
