import { ServicePage } from "@/components/service-page";

export const metadata = {
  title: "Notre Réseau de Transporteurs",
  description:
    "Sierra Logistics s'appuie sur un réseau solide de transporteurs partenaires sélectionnés pour la fiabilité et la sécurité.",
};

export default function TransporteursPage() {
  return (
    <ServicePage
      title="Notre Réseau de Transporteurs Partenaires"
      heroImage="/images/service-image-1.png"
      heading="Collaborer avec les Meilleurs Transporteurs pour Votre Logistique"
      sideImage="/images/service-image-1.png"
      sideImageAlt="Camion Sierra Logistics sur la route"
      ctaHref="/inscription-camion"
      ctaLabel="Inscrire Votre Camion"
    >
      <p>
        Chez Sierra Logistics, nous nous appuyons sur un réseau solide et soigneusement
        sélectionné de transporteurs partenaires. Cette collaboration nous permet
        d&apos;étendre notre portée et de garantir que vos marchandises sont
        transportées par des professionnels fiables et expérimentés, utilisant des
        équipements adaptés et sécurisés.
      </p>
      <p>Les avantages de notre réseau de transporteurs incluent :</p>
      <ul>
        <li>
          <strong>Sélection Rigoureuse :</strong> Nous travaillons uniquement avec des
          transporteurs qui respectent nos normes strictes de sécurité, de fiabilité et
          de service client.
        </li>
        <li>
          <strong>Couverture Étendue :</strong> Notre réseau nous permet de desservir
          efficacement diverses destinations à travers la sous-région Ouest Africaine.
        </li>
        <li>
          <strong>Flexibilité :</strong> Nous pouvons mobiliser les ressources
          nécessaires pour répondre aux besoins spécifiques de nos clients, qu&apos;il
          s&apos;agisse de lots complets, de lots partiels ou de marchandises
          spécialisées.
        </li>
        <li>
          <strong>Intégration Technologique :</strong> Nos transporteurs sont intégrés à
          nos systèmes de suivi pour une visibilité complète sur le parcours et la
          traçabilité des véhicules.
        </li>
        <li>
          <strong>Engagement Qualité :</strong> Nous partageons avec nos transporteurs
          une culture de l&apos;excellence et un engagement envers la livraison dans les
          délais et en parfait état.
        </li>
      </ul>
      <p>
        En choisissant Sierra Logistics, vous bénéficiez de l&apos;expertise et de la
        capacité combinées de notre organisation et de nos partenariats. Nous gérons la
        complexité de la coordination, vous offrant une solution de transport simple,
        efficace et fiable.
      </p>
    </ServicePage>
  );
}
