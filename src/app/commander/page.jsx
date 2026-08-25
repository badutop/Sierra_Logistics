import { CommanderForm } from "./commander-form";

export const metadata = {
  title: "Commander un Camion",
  description: "Retrouvez votre devis par numéro de téléphone pour commander un camion.",
};

export default function CommanderPage() {
  return (
    <>
      <section
        className="relative mt-[60px] flex min-h-[45vh] items-center bg-cover bg-center pt-20 text-white"
        style={{ backgroundImage: "url('/images/image.png')" }}
      >
        <div className="absolute inset-0 bg-black/50" />
        <div className="relative z-10 mx-auto w-[90%] max-w-6xl px-4 py-10 text-center [text-shadow:1px_1px_3px_rgba(0,0,0,0.5)]">
          <h1 className="text-3xl font-bold md:text-5xl">Commandez un Camion</h1>
        </div>
      </section>

      <section className="mx-auto w-[90%] max-w-3xl py-16">
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
