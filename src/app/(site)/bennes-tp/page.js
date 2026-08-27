import { ServicePage } from "@/components/service-page";

export const metadata = {
  title: "Approvisionnements par Bennes TP",
  description:
    "Approvisionnement de chantier par bennes TP de 18 à 55 m³ et location de camions 6x4 et 8x4 partout au Sénégal.",
};

export default function BennesTpPage() {
  return (
    <ServicePage
      title="Approvisionnement de Chantier par Bennes TP"
      heroImage="/images/service-bennes-tp.jpg"
      heading="Des Bennes TP pour Tous Vos Chantiers"
      sideImage="/images/realisation-manutention-ter.jpg"
      sideImageAlt="Opération de manutention sur chantier"
      ctaHref="/devis"
      ctaLabel="Demander un Devis pour l'Approvisionnement de Chantier"
    >
      <p>
        Sierra Logistics organise pour vous les approvisionnements de chantier, ainsi
        que la mise à disposition de véhicules directement sur site. Nous proposons des
        bennes de <strong>18 à 55 m³</strong>, ainsi que la location de camions{" "}
        <strong>6x4 et 8x4</strong>, sur tout l&apos;étendue du territoire sénégalais.
      </p>
      <p>Nos services incluent :</p>
      <ul>
        <li>Bennes TP de 18 à 55 m³ adaptées à tous types de matériaux</li>
        <li>Location de camions 6x4 et 8x4</li>
        <li>Approvisionnement au départ des différentes carrières sénégalaises</li>
        <li>Livraison directe sur chantier, dans tout le pays</li>
        <li>Mobilisation rapide pour répondre aux impératifs de vos travaux</li>
      </ul>
      <p>
        Que ce soit pour des chantiers de bâtiment, de travaux publics ou
        d&apos;infrastructures, notre flotte et nos chauffeurs expérimentés vous
        garantissent un approvisionnement régulier et fiable, au rythme de votre
        chantier.
      </p>
    </ServicePage>
  );
}
