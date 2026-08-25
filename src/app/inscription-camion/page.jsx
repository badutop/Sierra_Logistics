import { InscriptionCamionForm } from "./inscription-camion-form";

export const metadata = {
  title: "Inscription de Camion",
  description:
    "Rejoignez le réseau de transporteurs partenaires de Sierra Logistics en inscrivant votre camion.",
};

export default function InscriptionCamionPage() {
  return (
    <>
      <section
        className="relative mt-[60px] flex min-h-[45vh] items-center bg-cover bg-center pt-20 text-white"
        style={{ backgroundImage: "url('/images/service-image-1.png')" }}
      >
        <div className="absolute inset-0 bg-black/50" />
        <div className="relative z-10 mx-auto w-[90%] max-w-6xl px-4 py-10 text-center [text-shadow:1px_1px_3px_rgba(0,0,0,0.5)]">
          <h1 className="text-3xl font-bold md:text-5xl">Inscrire un Camion</h1>
        </div>
      </section>

      <section className="mx-auto w-[90%] max-w-3xl py-16">
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
