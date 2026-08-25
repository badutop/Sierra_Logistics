import { DevisForm } from "./devis-form";

export const metadata = {
  title: "Demander un Devis",
  description:
    "Obtenez un devis personnalisé pour votre transport au Sénégal en quelques minutes.",
};

export default function DevisPage() {
  return (
    <>
      <section
        className="relative mt-[60px] flex min-h-[45vh] items-center bg-cover bg-center pt-20 text-white"
        style={{ backgroundImage: "url('/images/service-image-2.png')" }}
      >
        <div className="absolute inset-0 bg-black/50" />
        <div className="relative z-10 mx-auto w-[90%] max-w-6xl px-4 py-10 text-center [text-shadow:1px_1px_3px_rgba(0,0,0,0.5)]">
          <h1 className="text-3xl font-bold md:text-5xl">Demander un Devis</h1>
        </div>
      </section>

      <section className="mx-auto w-[90%] max-w-3xl py-16">
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
