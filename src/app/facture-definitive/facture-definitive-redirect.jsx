"use client";

import { useEffect } from "react";
import { useRouter, useSearchParams } from "next/navigation";

// L'ancienne page facture-definitive.html lisait une table `factures` que
// rien, dans le site d'origine, n'alimente jamais (le seul flux qui écrit
// vraiment des données est facture-proforma.html -> table `commandes`).
// On redirige donc vers la vue définitive de /facture-proforma, qui est la
// seule implémentation réellement fonctionnelle de ce dernier écran.
//
// Redirection côté client (useRouter) plutôt que redirect() serveur : avec
// l'export statique du front (voir next.config.mjs), il n'y a pas de serveur
// pour lire searchParams au moment de la requête.
export function FactureDefinitiveRedirect() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const id = searchParams.get("id");

  useEffect(() => {
    router.replace(id ? `/facture-proforma?id=${id}&definitive=true` : "/facture-proforma");
  }, [id, router]);

  return null;
}
