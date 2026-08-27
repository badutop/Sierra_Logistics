import { ServicePage } from "@/components/service-page";

export const metadata = {
  title: "Transport pour la Campagne Arachidière",
  description:
    "Retour sur la mobilisation de Sierra Logistics pendant la campagne arachidière au Sénégal : collecte, transformation et export.",
};

export default function TransportArachidePage() {
  return (
    <ServicePage
      title="Transport pour la Campagne Arachidière"
      heroImage="/images/realisation-transport-arachide.jpg"
      heading="Une Flotte Mobilisée pour la Campagne Arachidière"
      sideImage="/images/service-transport-conteneur.jpg"
      sideImageAlt="Transport de conteneurs par Sierra Logistics"
      ctaHref="/devis"
      ctaLabel="Demander un Devis pour la Prochaine Campagne"
    >
      <p>
        Chaque année, la <strong>campagne arachidière</strong> mobilise l&apos;ensemble
        de la chaîne logistique sénégalaise pour acheminer les récoltes depuis les
        zones de production jusqu&apos;aux points de collecte, de transformation et
        d&apos;exportation. Sierra Logistics met sa flotte et son expérience au
        service de cette période stratégique pour l&apos;économie du pays.
      </p>
      <p>Notre intervention a couvert :</p>
      <ul>
        <li>La collecte des récoltes auprès des points d&apos;achat</li>
        <li>Le transport vers les huileries et unités de transformation</li>
        <li>L&apos;acheminement vers les terminaux d&apos;exportation du Port de Dakar</li>
        <li>Une mobilisation renforcée de la flotte durant toute la campagne</li>
      </ul>
      <p>
        La réussite de la campagne arachidière repose sur la capacité à mobiliser
        rapidement des camions sur l&apos;ensemble du territoire. Sierra Logistics
        s&apos;appuie sur son réseau de transporteurs partenaires pour répondre à cette
        forte demande saisonnière, tout en maintenant un haut niveau de fiabilité.
      </p>
    </ServicePage>
  );
}
