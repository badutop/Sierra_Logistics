import Link from "next/link";
import { ArrowLeft } from "lucide-react";

export function BackHomeLink({ className = "" }) {
  return (
    <Link
      href="/"
      className={`mb-8 inline-flex items-center gap-1.5 rounded-full bg-brand-accent px-4 py-1.5 text-sm font-semibold text-brand-accent-foreground shadow-md transition-opacity hover:opacity-90 ${className}`}
    >
      <ArrowLeft className="size-4" />
      Retour à l&apos;accueil
    </Link>
  );
}
