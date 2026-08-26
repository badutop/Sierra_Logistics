import { ServicePage } from "@/components/service-page";

export const metadata = {
  title: "Location de Camions",
  description:
    "Location de camions avec ou sans chauffeur à Dakar, partout au Sénégal et dans la sous-région, courte ou longue durée.",
};

export default function LocationCamionsPage() {
  return (
    <ServicePage
      title="Location de Camions à Dakar et dans Tout le Sénégal"
      heroImage="/images/service-location-camions.jpg"
      heading="Une Flotte de Camions Disponible Selon Vos Besoins"
      sideImage="/images/realisation-transfert-cereales.jpg"
      sideImageAlt="Camions Sierra Logistics prêts à charger"
      ctaHref="/devis"
      ctaLabel="Demander un Devis pour la Location de Camions"
    >
      <p>
        Sierra Logistics est une référence de la location de camions à Dakar, partout
        au Sénégal et dans la sous-région. Que vous ayez besoin d&apos;un véhicule pour
        une opération ponctuelle ou sur une plus longue durée, nous mettons à votre
        disposition une flotte adaptée à votre activité et à la nature de vos
        marchandises.
      </p>
      <p>Nos formules de location incluent :</p>
      <ul>
        <li>Location avec ou sans chauffeur</li>
        <li>Camions porteurs et semi-remorques</li>
        <li>Location courte durée (ponctuelle) et longue durée</li>
        <li>Véhicules entretenus et conformes aux normes de sécurité</li>
        <li>Mise à disposition rapide sur Dakar et l&apos;ensemble du territoire</li>
      </ul>
      <p>
        Chaque camion de notre flotte est régulièrement entretenu et nos chauffeurs
        sont sélectionnés pour leur expérience du réseau routier sénégalais, afin de
        vous garantir un service fiable, sécurisé et adapté à votre budget.
      </p>
    </ServicePage>
  );
}
