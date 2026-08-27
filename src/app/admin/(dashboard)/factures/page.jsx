"use client";

import { useEffect, useMemo, useState } from "react";
import Link from "next/link";
import { Search } from "lucide-react";
import { supabase } from "@/lib/supabaseClient";
import { formatNumber } from "@/lib/pricing";
import { Input } from "@/components/ui/input";

export default function AdminFacturesPage() {
  const [quotes, setQuotes] = useState(null);
  const [search, setSearch] = useState("");

  useEffect(() => {
    supabase
      .from("quotes")
      .select("*")
      .order("created_at", { ascending: false })
      .then(({ data }) => setQuotes(data || []));
  }, []);

  const filtered = useMemo(() => {
    if (!quotes) return [];
    const term = search.trim().toLowerCase();
    if (!term) return quotes;
    return quotes.filter((q) =>
      [q.nom, q.telephone, q.email, q.ville_depart, q.ville_arrivee]
        .filter(Boolean)
        .some((field) => field.toLowerCase().includes(term))
    );
  }, [quotes, search]);

  return (
    <div>
      <h1 className="text-2xl font-bold text-primary">Factures</h1>
      <p className="mt-1 text-sm text-muted-foreground">
        Tous les devis / factures proforma générés depuis le site.
      </p>

      <div className="relative mt-6 max-w-sm">
        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
        <Input
          placeholder="Rechercher par nom, téléphone, email, ville..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="pl-9"
        />
      </div>

      <div className="mt-6 overflow-x-auto rounded-lg bg-background shadow-sm">
        <table className="w-full text-left text-sm">
          <thead>
            <tr className="border-b bg-muted">
              <th className="p-3">Client</th>
              <th className="p-3">Trajet</th>
              <th className="p-3">Montant</th>
              <th className="p-3">Statut</th>
              <th className="p-3">Date</th>
              <th className="p-3"></th>
            </tr>
          </thead>
          <tbody>
            {quotes === null && (
              <tr>
                <td className="p-4 text-muted-foreground" colSpan={6}>
                  Chargement...
                </td>
              </tr>
            )}
            {quotes !== null && filtered.length === 0 && (
              <tr>
                <td className="p-4 text-muted-foreground" colSpan={6}>
                  Aucun résultat.
                </td>
              </tr>
            )}
            {filtered.map((q) => (
              <tr key={q.id} className="border-b last:border-none hover:bg-muted/50">
                <td className="p-3">
                  <p className="font-semibold text-primary">{q.nom || "-"}</p>
                  <p className="text-xs text-muted-foreground">{q.telephone}</p>
                </td>
                <td className="p-3">
                  {q.ville_depart} → {q.ville_arrivee}
                </td>
                <td className="p-3 font-mono">{formatNumber(q.total)} FCFA</td>
                <td className="p-3">
                  <span
                    className={`rounded px-2 py-0.5 text-xs font-bold text-white ${
                      q.statut === "commandé" ? "bg-green-600" : "bg-orange-500"
                    }`}
                  >
                    {q.statut === "commandé" ? "COMMANDÉ" : "EN ATTENTE"}
                  </span>
                </td>
                <td className="p-3 text-muted-foreground">
                  {q.created_at ? new Date(q.created_at).toLocaleDateString("fr-FR") : "-"}
                </td>
                <td className="p-3 text-right">
                  <Link href={`/admin/factures/${q.id}`} className="font-semibold text-brand-accent hover:underline">
                    Voir
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
