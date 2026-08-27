import { ServicePage } from "@/components/service-page";

export const metadata = {
  title: "Manutention, Levage et Transfert de Matériels sur le TER",
  description:
    "Retour sur des opérations de manutention, levage et transfert de matériels menées par Sierra Logistics dans le cadre des travaux du TER.",
};

export default function ManutentionTerPage() {
  return (
    <ServicePage
      title="Manutention, Levage et Transfert de Matériels sur le TER"
      heroImage="/images/realisation-manutention-ter.jpg"
      heading="Manutention et Levage sur un Chantier d'Infrastructure"
      sideImage="/images/service-bennes-tp.jpg"
      sideImageAlt="Approvisionnement de chantier par Sierra Logistics"
      ctaHref="/devis"
      ctaLabel="Demander un Devis pour une Opération Similaire"
    >
      <p>
        Sierra Logistics a participé à des opérations de manutention, de levage et de
        transfert de matériels dans le cadre des travaux d&apos;infrastructure du{" "}
        <strong>TER</strong> (Train Express Régional) de Dakar. Nos équipes ont assuré
        la mobilisation d&apos;engins et de personnel qualifié pour la manutention de
        matériaux directement sur le site du chantier.
      </p>
      <p>Nos interventions ont couvert :</p>
      <ul>
        <li>Le levage et la manutention de matériaux lourds (ferraille, matériels)</li>
        <li>Le transfert de matériels sur un site de chantier ferroviaire</li>
        <li>Un personnel équipé et formé aux normes de sécurité sur chantier</li>
        <li>La coordination avec les équipes de génie civil sur site</li>
      </ul>
      <p>
        Ce type de mission illustre la capacité de Sierra Logistics à intervenir sur
        des chantiers d&apos;infrastructures d&apos;envergure, en garantissant la
        sécurité des opérations et le respect des délais imposés par les grands
        projets nationaux.
      </p>
    </ServicePage>
  );
}
