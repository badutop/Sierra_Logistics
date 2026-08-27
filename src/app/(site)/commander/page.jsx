import { PageHero } from "@/components/page-hero";
import { BackHomeLink } from "@/components/back-home-link";
import { CommanderForm } from "./commander-form";

export const metadata = {
  title: "Commander un Camion",
  description: "Retrouvez votre devis par numéro de téléphone pour commander un camion.",
};

export default function CommanderPage() {
  return (
    <>
      <PageHero title="Commandez un Camion" image="/images/image.png" />

      <section className="mx-auto w-[90%] max-w-3xl py-16">
        <BackHomeLink />
        <h2 className="section-heading mx-auto block text-center">
          Obtenez un Camion pour votre Expédition
        </h2>
        <p className="mt-8 text-foreground">
          Veuillez saisir votre numéro de téléphone pour retrouver votre devis.
        </p>

        <CommanderForm />
      </section>
    </>
  );
}
