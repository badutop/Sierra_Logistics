import { PageHero } from "@/components/page-hero";
import { BackHomeLink } from "@/components/back-home-link";
import { DevisForm } from "./devis-form";

export const metadata = {
  title: "Demander un Devis",
  description:
    "Obtenez un devis personnalisé pour votre transport au Sénégal en quelques minutes.",
};

export default function DevisPage() {
  return (
    <>
      <PageHero title="Demander un Devis" image="/images/service-image-2.png" />

      <section className="mx-auto w-[90%] max-w-3xl py-16">
        <BackHomeLink />
        <h2 className="section-heading mx-auto block text-center">
          Obtenez un Devis Personnalisé
        </h2>
        <p className="mt-8 text-foreground">
          Veuillez remplir le formulaire ci-dessous avec les détails de votre
          expédition, et nous vous enverrons un devis personnalisé dans les plus brefs
          délais.
        </p>

        <DevisForm />
      </section>
    </>
  );
}
