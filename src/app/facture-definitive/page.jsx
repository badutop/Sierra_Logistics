import { redirect } from "next/navigation";

export const metadata = {
  title: "Facture Définitive",
  robots: { index: false },
};

// L'ancienne page facture-definitive.html lisait une table `factures` que
// rien, dans le site d'origine, n'alimente jamais (le seul flux qui écrit
// vraiment des données est facture-proforma.html -> table `commandes`).
// On redirige donc vers la vue définitive de /facture-proforma, qui est la
// seule implémentation réellement fonctionnelle de ce dernier écran.
export default async function FactureDefinitivePage({ searchParams }) {
  const { id } = await searchParams;
  redirect(id ? `/facture-proforma?id=${id}&definitive=true` : "/facture-proforma");
}
