import { ServicePage } from "@/components/service-page";

export const metadata = {
  title: "Transport de Conteneurs",
  description:
    "Transport routier de conteneurs maritimes au Sénégal : enlèvement au Port de Dakar, conteneurs standards et frigorifiques.",
};

export default function TransportConteneursPage() {
  return (
    <ServicePage
      title="Transport de Conteneurs Maritimes au Sénégal"
      heroImage="/images/service-transport-conteneur.jpg"
      heading="Un Service de Transport de Conteneurs Sur Mesure"
      sideImage="/images/realisation-transport-arachide.jpg"
      sideImageAlt="Camion transportant un conteneur maritime"
      ctaHref="/devis"
      ctaLabel="Demander un Devis pour le Transport de Conteneurs"
    >
      <p>
        Notre entreprise de transport routier de conteneurs maritimes vous propose un
        service sur mesure pour déplacer, dans les meilleures conditions, vos caisses
        de marchandises arrivées au <strong>Port Autonome de Dakar</strong> ou vos
        conteneurs en partance pour un voyage maritime. Spécialisés également dans le
        transport de conteneurs frigorifiques, nous nous rendons disponibles dans tout
        le Sénégal et dans la sous-région pour vous accompagner dans vos projets de
        déplacement.
      </p>
      <p>Nos services incluent :</p>
      <ul>
        <li>Transport de conteneurs standards (20 et 40 pieds)</li>
        <li>Transport de conteneurs frigorifiques (reefer)</li>
        <li>Enlèvement et livraison depuis et vers le Port de Dakar</li>
        <li>Transport de conteneurs vers la sous-région ouest-africaine</li>
        <li>Suivi de l&apos;acheminement de vos conteneurs</li>
      </ul>
      <p>
        Avec des tracteurs routiers adaptés au transport de conteneurs et des
        chauffeurs expérimentés dans les procédures portuaires, Sierra Logistics vous
        garantit un enlèvement rapide et une livraison en toute sécurité, dans le
        respect des délais des compagnies maritimes.
      </p>
    </ServicePage>
  );
}
