import { ServicePage } from "@/components/service-page";

export const metadata = {
  title: "Technologie",
  description:
    "Suivi GPS en temps réel, plateforme de gestion logistique et optimisation des itinéraires chez Sierra Logistics.",
};

export default function TechnologiePage() {
  return (
    <ServicePage
      title="La Technologie au Service de Votre Logistique"
      heroImage="/images/feature-image-2.png"
      heading="Suivi en Temps Réel et Gestion Optimisée"
      sideImages={[
        { src: "/images/Sierra-GPS.jpeg", alt: "Suivi GPS en temps réel des véhicules Sierra Logistics" },
        { src: "/images/Logicielsierra.png", alt: "Plateforme de gestion logistique Sierra Logistics" },
      ]}
      ctaHref="/devis"
      ctaLabel="Demander un Devis"
    >
      <p>
        Chez Sierra Logistics, nous exploitons la puissance de la technologie pour
        rendre vos opérations logistiques plus efficaces, transparentes et sécurisées.
        Notre investissement dans des solutions numériques de pointe nous permet
        d&apos;offrir un service supérieur, de la planification à la livraison finale.
      </p>
      <p>Les avantages de notre approche technologique incluent :</p>
      <ul>
        <li>
          <strong>Suivi GPS en Temps Réel :</strong> Sachez où se trouvent vos
          marchandises à chaque instant.
        </li>
        <li>
          <strong>Plateforme de Gestion Logistique :</strong> Un accès simple pour gérer
          vos expéditions, devis et historique.
        </li>
        <li>
          <strong>Optimisation des Itinéraires :</strong> Utilisation d&apos;algorithmes
          pour les parcours les plus rapides et efficaces.
        </li>
        <li>
          <strong>Communication Améliorée :</strong> Des mises à jour instantanées et
          une communication fluide avec nos équipes.
        </li>
        <li>
          <strong>Sécurité des Données :</strong> Protection robuste de vos informations
          et des détails de vos expéditions.
        </li>
        <li>
          <strong>Rapports Détaillés :</strong> Accès à des analyses de performance pour
          un meilleur contrôle.
        </li>
      </ul>
      <p>
        Nous croyons que la technologie est un levier essentiel pour la logistique
        moderne. En choisissant Sierra Logistics, vous bénéficiez non seulement
        d&apos;un service fiable, mais aussi d&apos;une visibilité et d&apos;un contrôle
        accrus sur votre chaîne d&apos;approvisionnement, vous permettant de prendre des
        décisions éclairées et d&apos;optimiser vos coûts.
      </p>
    </ServicePage>
  );
}
