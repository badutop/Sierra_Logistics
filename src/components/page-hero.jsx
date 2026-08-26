import Link from "next/link";
import { ArrowLeft } from "lucide-react";

export function PageHero({ title, image }) {
  return (
    <section
      className="relative mt-[60px] flex min-h-[45vh] items-center bg-cover bg-center pt-20 text-white"
      style={{ backgroundImage: `url('${image}')` }}
    >
      <div className="absolute inset-0 bg-black/50" />
      <div className="relative z-10 mx-auto w-[90%] max-w-6xl px-4 py-10 [text-shadow:1px_1px_3px_rgba(0,0,0,0.5)]">
        <Link
          href="/"
          className="mb-6 inline-flex items-center gap-1.5 rounded-full bg-brand-accent px-4 py-1.5 text-sm font-semibold text-brand-accent-foreground shadow-md transition-opacity hover:opacity-90"
        >
          <ArrowLeft className="size-4" />
          Retour à l&apos;accueil
        </Link>
        <h1 className="text-center text-3xl font-bold md:text-5xl">{title}</h1>
      </div>
    </section>
  );
}
