import { ServicePage } from "@/components/service-page";

export const metadata = {
  title: "Opération d'Empotage de Blé",
  description:
    "Retour sur des opérations d'empotage de blé au Port Autonome de Dakar menées par Sierra Logistics.",
};

export default function EmpotageBlePage() {
  return (
    <ServicePage
      title="Opération d'Empotage de Blé au Port Autonome de Dakar"
      heroImage="/images/realisation-empotage-ble.jpg"
      heading="Réception et Empotage de Blé en Vrac"
      sideImage="/images/realisation-transfert-cereales.jpg"
      sideImageAlt="Manutention de céréales par Sierra Logistics"
      ctaHref="/devis"
      ctaLabel="Demander un Devis pour une Opération Similaire"
    >
      <p>
        Dans le cadre de plusieurs opérations d&apos;empotage de blé au{" "}
        <strong>Port Autonome de Dakar</strong>, les équipes de Sierra Logistics ont
        assuré la réception du blé en vrac directement depuis la cale des navires,
        pour le charger dans des bennes et remorques dédiées au transport vers les
        minoteries.
      </p>
      <p>Nos interventions ont couvert :</p>
      <ul>
        <li>La réception de marchandises en vrac en zone portuaire</li>
        <li>L&apos;empotage sous portique et grue de manutention portuaire</li>
        <li>Le transport vers les minoteries et usines de transformation</li>
        <li>La coordination avec les équipes et procédures portuaires</li>
      </ul>
      <p>
        Ce type d&apos;opération demande une parfaite coordination entre les équipes
        portuaires et nos équipes de transport, afin d&apos;assurer un enlèvement
        rapide et une livraison sans rupture de la chaîne d&apos;approvisionnement.
      </p>
    </ServicePage>
  );
}
