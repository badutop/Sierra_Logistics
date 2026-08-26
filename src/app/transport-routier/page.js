import { ServicePage } from "@/components/service-page";

export const metadata = {
  title: "Transport Routier",
  description:
    "Transport routier fiable au Sénégal : lots complets et partiels, marchandises générales, livraisons express.",
};

export default function TransportRoutierPage() {
  return (
    <ServicePage
      title="Transport Routier Fiable au Sénégal"
      heroImage="/images/service-image-1.png"
      heading="Solutions de Transport Routier sur Mesure"
      sideImage="/images/service-location-camions.jpg"
      sideImageAlt="Camions Sierra Logistics prêts pour le transport routier"
      ctaHref="/#quick-quote"
      ctaLabel="Demander un Devis pour le Transport Routier"
    >
      <p>
        Chez Sierra Logistics, nous comprenons l&apos;importance cruciale d&apos;un
        transport routier fiable et efficace pour le succès de votre entreprise. Nous
        offrons une gamme complète de services de transport de marchandises à travers
        tout le Sénégal, adaptés à vos besoins spécifiques.
      </p>
      <p>Nos services inclus :</p>
      <ul>
        <li>Transport de lots complets (FTL) et lots partiels (LTL)</li>
        <li>Transport de marchandises générales</li>
        <li>Transport sous température contrôlée (sur demande)</li>
        <li>Livraisons express et planifiées</li>
        <li>Solutions de transport dédiées</li>
      </ul>
      <p>
        Avec une flotte de véhicules modernes et bien entretenus, et des chauffeurs
        expérimentés connaissant parfaitement le réseau routier sénégalais, nous
        garantissons la sécurité et la ponctualité de vos livraisons. Notre technologie
        de suivi vous permet de rester informé de l&apos;état de votre expédition en
        temps réel.
      </p>
    </ServicePage>
  );
}
