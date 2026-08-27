"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { supabase } from "@/lib/supabaseClient";

export default function AdminCommandesPage() {
  const [rows, setRows] = useState(null);

  useEffect(() => {
    (async () => {
      const { data: commandes } = await supabase
        .from("commandes")
        .select("*")
        .order("date_validation", { ascending: false });

      if (!commandes || commandes.length === 0) {
        setRows([]);
        return;
      }

      const quoteIds = commandes.map((c) => c.proforma_id);
      const { data: quotes } = await supabase.from("quotes").select("*").in("id", quoteIds);
      const quoteById = new Map((quotes || []).map((q) => [q.id, q]));

      setRows(commandes.map((c) => ({ ...c, quote: quoteById.get(c.proforma_id) })));
    })();
  }, []);

  return (
    <div>
      <h1 className="text-2xl font-bold text-primary">Commandes</h1>
      <p className="mt-1 text-sm text-muted-foreground">
        Commandes validées, avec le camion et le chauffeur affectés.
      </p>

      <div className="mt-6 overflow-x-auto rounded-lg bg-background shadow-sm">
        <table className="w-full text-left text-sm">
          <thead>
            <tr className="border-b bg-muted">
              <th className="p-3">Client</th>
              <th className="p-3">Trajet</th>
              <th className="p-3">Camion</th>
              <th className="p-3">Chauffeur</th>
              <th className="p-3">Date validation</th>
              <th className="p-3"></th>
            </tr>
          </thead>
          <tbody>
            {rows === null && (
              <tr>
                <td className="p-4 text-muted-foreground" colSpan={6}>
                  Chargement...
                </td>
              </tr>
            )}
            {rows !== null && rows.length === 0 && (
              <tr>
                <td className="p-4 text-muted-foreground" colSpan={6}>
                  Aucune commande validée pour le moment.
                </td>
              </tr>
            )}
            {rows?.map((c) => (
              <tr key={c.id} className="border-b last:border-none hover:bg-muted/50">
                <td className="p-3">
                  <p className="font-semibold text-primary">{c.quote?.nom || "-"}</p>
                  <p className="text-xs text-muted-foreground">{c.quote?.telephone}</p>
                </td>
                <td className="p-3">
                  {c.quote?.ville_depart} → {c.quote?.ville_arrivee}
                </td>
                <td className="p-3">{c.camion_immatriculation}</td>
                <td className="p-3">
                  {c.chauffeur}
                  <p className="text-xs text-muted-foreground">{c.telephone_chauffeur}</p>
                </td>
                <td className="p-3 text-muted-foreground">
                  {c.date_validation ? new Date(c.date_validation).toLocaleDateString("fr-FR") : "-"}
                </td>
                <td className="p-3 text-right">
                  <Link
                    href={`/admin/factures/${c.proforma_id}`}
                    className="font-semibold text-brand-accent hover:underline"
                  >
                    Voir la facture
                  </Link>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
