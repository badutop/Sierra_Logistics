import { ServicePage } from "@/components/service-page";

export const metadata = {
  title: "Suivi de Flotte",
  description:
    "Le suivi de flotte en temps réel de Sierra Logistics : géolocalisation, itinéraires et géofencing (en développement).",
};

export default function SuiviFlottePage() {
  return (
    <ServicePage
      title="Suivi de Flotte en Temps Réel"
      heroImage="/images/feature-image-2.png"
      heading="Une Visibilité Complète pour Propriétaires et Clients"
      sideImage="/images/feature-image-2.png"
      sideImageAlt="Personne utilisant une tablette pour le suivi"
    >
      <p>
        Notre solution de suivi de flotte vous offre une visibilité en temps réel sur le
        positionnement et le parcours de vos camions ou de vos expéditions. Que vous
        soyez propriétaire d&apos;un camion partenaire ou un client attendant une
        livraison, accédez aux informations clés pour une tranquillité d&apos;esprit
        totale.
      </p>
      <p>Notre outil de suivi (en développement) inclura :</p>
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
        Cette technologie vise à améliorer la sécurité, l&apos;efficacité et la
        transparence de vos opérations logistiques. Restez informé et gardez le
        contrôle, où que vous soyez.
      </p>
      <div className="mt-8 rounded-lg bg-muted p-6 text-center">
        <h3 className="mb-2.5 font-semibold text-primary">
          Outil de Suivi Actuellement en Développement
        </h3>
        <p>
          La plateforme interactive de suivi de flotte est en cours de finalisation. En
          attendant, notre équipe reste à votre disposition pour toute information
          concernant vos expéditions ou vos camions.
        </p>
      </div>
    </ServicePage>
  );
}
