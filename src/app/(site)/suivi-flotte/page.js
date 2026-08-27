import { ServicePage } from "@/components/service-page";

export const metadata = {
  title: "Suivi de Flotte",
  description:
    "Le suivi de flotte en temps réel de Sierra Logistics : géolocalisation, itinéraires et géofencing.",
};

export default function SuiviFlottePage() {
  return (
    <ServicePage
      title="Suivi de Flotte en Temps Réel"
      heroImage="/images/feature-image-2.png"
      heading="Une Visibilité Complète pour Propriétaires et Clients"
      sideImages={[
        { src: "/images/Sierra-GPS.jpeg", alt: "Suivi GPS en temps réel des véhicules Sierra Logistics" },
        { src: "/images/Logicielsierra.png", alt: "Plateforme de gestion logistique Sierra Logistics" },
      ]}
      ctaHref="/devis"
      ctaLabel="Demander un Devis"
    >
      <p>
        Notre solution de suivi de flotte vous offre une visibilité en temps réel sur le
        positionnement et le parcours de vos camions ou de vos expéditions. Que vous
        soyez propriétaire d&apos;un camion partenaire ou un client attendant une
        livraison, accédez aux informations clés pour une tranquillité d&apos;esprit
        totale.
      </p>
      <p>Notre outil de suivi comprend :</p>
      <ul>
        <li>Géolocalisation en Direct : Visualisez l&apos;emplacement exact des camions sur une carte.</li>
        <li>Tracé des Itinéraires : Suivez le chemin parcouru et vérifiez la conformité aux itinéraires planifiés.</li>
        <li>
          Géofencing : Recevez des alertes lors de l&apos;entrée ou de la sortie de zones
          définies (entrepôts, points de livraison, etc.).
        </li>
        <li>Historique des Trajets : Consultez les données de trajets passés.</li>
        <li>Statut de Livraison : Obtenez des mises à jour sur les étapes clés de l&apos;expédition.</li>
      </ul>
      <p>
        Cette technologie améliore la sécurité, l&apos;efficacité et la transparence de
        vos opérations logistiques. Restez informé et gardez le contrôle, où que vous
        soyez.
      </p>
    </ServicePage>
  );
}
