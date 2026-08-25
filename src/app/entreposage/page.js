import { ServicePage } from "@/components/service-page";

export const metadata = {
  title: "Entreposage",
  description:
    "Solutions d'entreposage sécurisées et flexibles au Sénégal : stockage, gestion des stocks, cross-docking.",
};

export default function EntreposagePage() {
  return (
    <ServicePage
      title="Solutions d'Entreposage Sécurisées et Flexibles"
      heroImage="/images/service-image-2.png"
      heading="Optimisez Votre Stockage avec Nos Solutions d'Entreposage"
      sideImage="/images/service-image-2.png"
      sideImageAlt="Vue intérieure d'un entrepôt moderne"
      ctaHref="/devis"
      ctaLabel="Demander un Devis pour l'Entreposage"
    >
      <p>
        Sierra Logistics offre des solutions d&apos;entreposage modernes et sécurisées
        pour répondre à tous vos besoins de stockage au Sénégal. Nos installations sont
        conçues pour garantir l&apos;intégrité et la sécurité de vos marchandises,
        quelle que soit leur nature.
      </p>
      <p>Nos capacités d&apos;entreposage inclus :</p>
      <ul>
        <li>Stockage à court et long terme</li>
        <li>Gestion des stocks informatisée</li>
        <li>Préparation de commandes (picking &amp; packing)</li>
        <li>Cross-docking</li>
        <li>Installations sécurisées avec surveillance 24/7</li>
        <li>Options de stockage sous température contrôlée (sur demande)</li>
      </ul>
      <p>
        Nous proposons des solutions flexibles adaptées à la taille et aux exigences
        spécifiques de votre entreprise. Notre équipe expérimentée assure une gestion
        efficace de vos stocks, vous permettant de vous concentrer sur votre cœur de
        métier. Profitez d&apos;un accès facile à vos marchandises et d&apos;une
        visibilité complète sur vos inventaires grâce à nos systèmes de gestion
        avancés.
      </p>
    </ServicePage>
  );
}
