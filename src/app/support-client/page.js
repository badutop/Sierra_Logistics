import { ServicePage } from "@/components/service-page";

export const metadata = {
  title: "Support Client",
  description:
    "Une équipe Sierra Logistics dédiée et disponible 24/7 pour vous accompagner à chaque étape de votre logistique.",
};

export default function SupportClientPage() {
  return (
    <ServicePage
      title="Support Client Dédié à Votre Service"
      heroImage="/images/feature-image-3.png"
      heading="Une Équipe à Votre Écoute 24/7"
      sideImage="/images/feature-image-3.png"
      sideImageAlt="Agent de support client souriant"
      ctaHref="/devis"
      ctaLabel="Demander un Devis"
    >
      <p>
        Chez Sierra Logistics, un support client réactif et personnalisé est au cœur de
        nos engagements. Notre équipe dédiée est disponible pour vous accompagner à
        chaque étape, répondre à vos questions et résoudre rapidement les éventuels
        problèmes.
      </p>
      <p>Notre support client vous offre :</p>
      <ul>
        <li>Assistance pour le suivi de vos expéditions en temps réel</li>
        <li>Aide pour l&apos;obtention de devis et la planification de transports</li>
        <li>Support technique pour nos plateformes et outils de gestion</li>
        <li>Résolution rapide et efficace des incidents ou des requêtes spécifiques</li>
        <li>Conseils personnalisés pour optimiser vos opérations logistiques</li>
      </ul>
      <p>
        Nous mettons tout en œuvre pour vous garantir une expérience fluide et sans
        souci. Contactez-nous par téléphone, email, ou via le formulaire de contact pour
        toute demande. Votre satisfaction est notre priorité.
      </p>
    </ServicePage>
  );
}
