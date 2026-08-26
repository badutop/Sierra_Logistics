import { PageHero } from "@/components/page-hero";
import { BackHomeLink } from "@/components/back-home-link";
import { InscriptionCamionForm } from "./inscription-camion-form";

export const metadata = {
  title: "Inscription de Camion",
  description:
    "Rejoignez le réseau de transporteurs partenaires de Sierra Logistics en inscrivant votre camion.",
};

export default function InscriptionCamionPage() {
  return (
    <>
      <PageHero title="Inscrire un Camion" image="/images/service-image-1.png" />

      <section className="mx-auto w-[90%] max-w-3xl py-16">
        <BackHomeLink />
        <h2 className="section-heading mx-auto block text-center">
          Rejoignez Notre Réseau de Transporteurs
        </h2>
        <p className="mt-8 text-foreground">
          Vous êtes propriétaire ou exploitant de camion ? Rejoignez notre réseau de
          transporteurs partenaires et accédez à de nouvelles opportunités. Remplissez
          le formulaire ci-dessous, et notre équipe vous recontactera rapidement.
        </p>

        <InscriptionCamionForm />
      </section>
    </>
  );
}
