"use client";

import { useEffect, useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import Image from "next/image";
import { supabase } from "@/lib/supabaseClient";
import { formatNumber } from "@/lib/pricing";
import { Button } from "@/components/ui/button";

export function FactureProformaView() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const quoteId = searchParams.get("id");
  const isDefinitive = searchParams.get("definitive") === "true";

  const [quote, setQuote] = useState(null);
  const [commande, setCommande] = useState(null);
  const [error, setError] = useState(null);
  const [validating, setValidating] = useState(false);

  useEffect(() => {
    if (!quoteId) return;

    (async () => {
      const { data, error: quoteError } = await supabase
        .from("quotes")
        .select("*")
        .eq("id", quoteId)
        .maybeSingle();

      if (quoteError || !data) {
        setError(quoteError?.message || "Devis introuvable");
        return;
      }
      setQuote(data);

      if (isDefinitive) {
        const res = await fetch(`/api/commandes?proformaId=${quoteId}`);
        const body = await res.json();

        if (!res.ok) {
          setError(body.error);
          return;
        }
        setCommande(body.commande);
      }
    })();
  }, [quoteId, isDefinitive]);

  async function validerCommande() {
    if (!window.confirm("Valider cette commande ?")) return;

    setValidating(true);
    try {
      const res = await fetch("/api/commandes/valider", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ quoteId }),
      });
      const body = await res.json();

      if (!res.ok) throw new Error(body.error);

      router.push(`/facture-proforma?id=${quoteId}&definitive=true`);
    } catch (err) {
      window.alert(`Erreur de validation: ${err.message}`);
    } finally {
      setValidating(false);
    }
  }

  if (!quoteId || error) {
    return (
      <div className="mx-auto my-10 w-[90%] max-w-3xl rounded-md border border-red-300 bg-red-50 p-6 text-red-700">
        <h2 className="text-xl font-bold">Erreur</h2>
        <p>{error || "ID manquant dans l'URL"}</p>
      </div>
    );
  }

  if (!quote) {
    return <div className="py-20 text-center text-muted-foreground">Chargement...</div>;
  }

  return (
    <div className="mx-auto my-10 w-[90%] max-w-4xl rounded-lg bg-background p-8 shadow-md print:shadow-none">
      <div className="mb-8 flex justify-between border-b-2 border-primary pb-5">
        <div>
          <h1 className="text-2xl font-bold text-primary">
            {isDefinitive ? "FACTURE DÉFINITIVE" : "FACTURE PROFORMA"}
          </h1>
          <p>
            <strong>N&deg;:</strong> {quote.id?.split("-")[0]}
          </p>
          <p>
            <strong>Date:</strong>{" "}
            {quote.created_at ? new Date(quote.created_at).toLocaleDateString("fr-FR") : "-"}
          </p>
          <span
            className={`mt-1 inline-block rounded px-2.5 py-1 text-sm font-bold text-white ${
              isDefinitive ? "bg-green-600" : "bg-orange-500"
            }`}
          >
            {isDefinitive ? "DÉFINITIVE" : "PROFORMA"}
          </span>
        </div>
        <div className="text-right">
          <Image src="/images/logo.png" alt="Logo Sierra Logistics" width={120} height={40} />
          <p className="mt-2">
            <strong>Statut:</strong> {isDefinitive ? "Validée" : "En attente"}
          </p>
        </div>
      </div>

      <section className="mb-8">
        <div className="mb-4 rounded bg-primary px-4 py-2 font-semibold text-primary-foreground">
          INFORMATIONS CLIENT
        </div>
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <p>
              <strong>Nom:</strong> {quote.nom || "-"}
            </p>
            <p>
              <strong>Téléphone:</strong> {quote.telephone || "-"}
            </p>
          </div>
          <div>
            <p>
              <strong>Email:</strong> {quote.email || "-"}
            </p>
            <p>
              <strong>Date d&apos;expédition:</strong> {quote.date_expedition || "-"}
            </p>
          </div>
        </div>
      </section>

      <section className="mb-8">
        <div className="mb-4 rounded bg-primary px-4 py-2 font-semibold text-primary-foreground">
          DÉTAILS DE L&apos;EXPÉDITION
        </div>
        <table className="w-full border-collapse text-left text-sm">
          <thead>
            <tr className="bg-muted">
              <th className="p-3">Ville départ</th>
              <th className="p-3">Ville arrivée</th>
              <th className="p-3">Type marchandise</th>
              <th className="p-3">Poids</th>
              <th className="p-3">Type camion</th>
            </tr>
          </thead>
          <tbody>
            <tr className="border-b">
              <td className="p-3">{quote.ville_depart || "-"}</td>
              <td className="p-3">{quote.ville_arrivee || "-"}</td>
              <td className="p-3">{quote.type_marchandise || "-"}</td>
              <td className="p-3">{quote.poids ? `${formatNumber(quote.poids)} kg` : "-"}</td>
              <td className="p-3">{quote.type_vehicle || "-"}</td>
            </tr>
          </tbody>
        </table>
      </section>

      <section className="mb-8">
        <div className="mb-4 rounded bg-primary px-4 py-2 font-semibold text-primary-foreground">
          INFORMATIONS TARIFAIRES
        </div>
        <table className="w-full text-sm">
          <tbody>
            <tr className="border-b">
              <td className="py-2 font-semibold">Transport base:</td>
              <td className="py-2 text-right font-mono">{formatNumber(quote.montant_transport)} FCFA</td>
            </tr>
            {quote.majoration > 0 && (
              <tr className="border-b">
                <td className="py-2 font-semibold">Majoration:</td>
                <td className="py-2 text-right font-mono">{formatNumber(quote.majoration)} FCFA</td>
              </tr>
            )}
            <tr className="border-b">
              <td className="py-2 font-semibold">Sous-total:</td>
              <td className="py-2 text-right font-mono font-bold">{formatNumber(quote.sous_total)} FCFA</td>
            </tr>
            <tr className="border-b">
              <td className="py-2 font-semibold">TVA (18%):</td>
              <td className="py-2 text-right font-mono">{formatNumber(quote.tva)} FCFA</td>
            </tr>
            <tr className="bg-muted">
              <td className="py-2 pl-2 font-bold text-primary">TOTAL:</td>
              <td className="py-2 pr-2 text-right font-mono text-lg font-bold text-primary">
                {formatNumber(quote.total)} FCFA
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      {isDefinitive && commande && (
        <section className="mb-8">
          <div className="mb-4 rounded bg-primary px-4 py-2 font-semibold text-primary-foreground">
            EXÉCUTION DE LA COMMANDE
          </div>
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <p>
                <strong>Camion affecté:</strong> {commande.camion_immatriculation || "-"}
              </p>
              <p>
                <strong>Chauffeur:</strong> {commande.chauffeur || "-"}
              </p>
            </div>
            <div>
              <p>
                <strong>Téléphone chauffeur:</strong> {commande.telephone_chauffeur || "-"}
              </p>
              <p>
                <strong>Date validation:</strong>{" "}
                {commande.date_validation
                  ? new Date(commande.date_validation).toLocaleDateString("fr-FR")
                  : "-"}
              </p>
            </div>
          </div>
        </section>
      )}

      <div className="flex gap-3 print:hidden">
        <Button variant="outline" onClick={() => window.print()}>
          Imprimer
        </Button>
        {!isDefinitive && (
          <Button
            onClick={validerCommande}
            disabled={validating}
            className="bg-brand-accent text-brand-accent-foreground hover:bg-brand-accent/90"
          >
            {validating ? "Validation..." : "Valider la commande"}
          </Button>
        )}
      </div>
    </div>
  );
}
