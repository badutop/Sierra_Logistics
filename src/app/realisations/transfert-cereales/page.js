import { ServicePage } from "@/components/service-page";

export const metadata = {
  title: "Opération Transfert de Céréales en Sacs",
  description:
    "Retour sur une opération de transfert de céréales conditionnées en sacs menée par Sierra Logistics au Sénégal.",
};

export default function TransfertCerealesPage() {
  return (
    <ServicePage
      title="Opération Transfert de Céréales en Sacs"
      heroImage="/images/realisation-transfert-cereales.jpg"
      heading="Manutention et Transfert de Céréales en Sacs"
      sideImage="/images/realisation-empotage-ble.jpg"
      sideImageAlt="Opération de manutention de céréales"
      ctaHref="/devis"
      ctaLabel="Demander un Devis pour une Opération Similaire"
    >
      <p>
        Sierra Logistics a pris en charge une opération de transfert de céréales
        conditionnées en sacs, depuis leur lieu de stockage jusqu&apos;à leur point de
        livraison. Ce type d&apos;opération exige une manutention précise et un
        arrimage soigné pour préserver l&apos;intégrité des sacs tout au long du
        trajet.
      </p>
      <p>Notre intervention a couvert :</p>
      <ul>
        <li>Le chargement et l&apos;arrimage sécurisé des sacs de céréales</li>
        <li>Une manutention adaptée au transport de denrées alimentaires</li>
        <li>La livraison vers minoteries, usines et entrepôts de stockage</li>
        <li>Le respect des délais propres à la chaîne agroalimentaire</li>
      </ul>
      <p>
        Grâce à des équipes expérimentées et à une flotte adaptée, Sierra Logistics
        garantit une manutention soignée et une traçabilité de bout en bout pour ce
        type d&apos;opération, essentielle à la chaîne d&apos;approvisionnement
        alimentaire au Sénégal.
      </p>
    </ServicePage>
  );
}
