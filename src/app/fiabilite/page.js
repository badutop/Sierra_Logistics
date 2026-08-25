import { ServicePage } from "@/components/service-page";

export const metadata = {
  title: "Fiabilité",
  description:
    "La fiabilité au cœur de l'engagement de Sierra Logistics : respect des délais et sécurité de vos marchandises.",
};

export default function FiabilitePage() {
  return (
    <ServicePage
      title="La Fiabilité au Cœur de Notre Engagement"
      heroImage="/images/feature-image-1.png"
      heading="Respect des Délais et Sécurité de Vos Marchandises"
      sideImage="/images/service-image-1.png"
      sideImageAlt="Transport routier fiable"
      ctaHref="/devis"
      ctaLabel="Demander un Devis Fiable"
    >
      <p>
        La fiabilité est la pierre angulaire des opérations de Sierra Logistics. Nous
        nous engageons à assurer que vos marchandises atteignent leur destination dans
        les délais impartis et en parfait état. Notre réputation repose sur notre
        capacité à livrer de manière constante et prévisible.
      </p>
      <p>Notre engagement envers la fiabilité se traduit par :</p>
      <ul>
        <li>Une planification méticuleuse de chaque trajet</li>
        <li>Des véhicules modernes régulièrement entretenus</li>
        <li>Des chauffeurs expérimentés et formés aux meilleures pratiques</li>
        <li>
          Des protocoles de sécurité stricts pour la manipulation et le transport des
          marchandises
        </li>
        <li>Un suivi rigoureux pour anticiper et gérer les imprévus</li>
        <li>Une communication transparente sur l&apos;état de vos expéditions</li>
      </ul>
      <p>
        Vous pouvez compter sur Sierra Logistics pour un service sur lequel vous pouvez
        bâtir. Nous comprenons que la ponctualité et la sécurité sont essentielles pour
        votre chaîne d&apos;approvisionnement. Faites confiance à notre expertise pour
        une logistique fiable, jour après jour.
      </p>
    </ServicePage>
  );
}
