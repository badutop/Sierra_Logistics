import { ServicePage } from "@/components/service-page";

export const metadata = {
  title: "Distribution Locale",
  description:
    "Distribution locale rapide et efficace jusqu'au dernier kilomètre, avec suivi en temps réel de vos colis.",
};

export default function DistributionLocalePage() {
  return (
    <ServicePage
      title="Distribution Locale Rapide et Efficace"
      heroImage="/images/service-image-3.png"
      heading="Solutions de Distribution de Dernier Kilomètre"
      sideImage="/images/service-image-3.png"
      sideImageAlt="Vue d'un véhicule de distribution locale"
      ctaHref="/devis"
      ctaLabel="Demander un Devis pour la Distribution Locale"
    >
      <p>
        Chez Sierra Logistics, nous comprenons l&apos;importance d&apos;une
        distribution locale efficace pour livrer vos marchandises aux clients finals de
        manière rapide et fiable. Nous offrons des solutions de distribution de dernier
        kilomètre adaptées à vos besoins, garantissant que vos produits arrivent à
        destination dans les délais et en parfait état.
      </p>
      <p>Nos services de distribution locale incluent :</p>
      <ul>
        <li>Livraison rapide et planifiée dans les zones urbaines et rurales</li>
        <li>Service de livraison la journée même ou le lendemain</li>
        <li>Suivi en temps réel de vos colis pour une visibilité totale</li>
        <li>Personnalisation des solutions de distribution selon vos besoins spécifiques</li>
        <li>
          Équipements et véhicules adaptés pour garantir la sécurité et l&apos;intégrité
          de vos marchandises
        </li>
      </ul>
      <p>
        Nos équipes sont formées pour offrir un service client de haute qualité, vous
        assurant une expérience positive et professionnelle. Nous nous engageons à
        répondre à vos besoins de distribution locale avec flexibilité et efficacité,
        vous permettant de vous concentrer sur votre cœur de métier tout en ayant la
        certitude que vos clients sont satisfaits.
      </p>
    </ServicePage>
  );
}
