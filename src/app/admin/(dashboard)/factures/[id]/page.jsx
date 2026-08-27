"use client";

import { useEffect, useState } from "react";
import { useParams } from "next/navigation";
import Link from "next/link";
import { Mail, MessageCircle, ExternalLink } from "lucide-react";
import { supabase } from "@/lib/supabaseClient";
import { formatNumber } from "@/lib/pricing";
import { toWhatsAppLink } from "@/lib/whatsapp";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

export default function AdminFactureDetailPage() {
  const { id } = useParams();
  const [quote, setQuote] = useState(null);
  const [commande, setCommande] = useState(null);
  const [email, setEmail] = useState("");
  const [sending, setSending] = useState(false);
  const [emailStatus, setEmailStatus] = useState(null);

  useEffect(() => {
    if (!id) return;
    supabase
      .from("quotes")
      .select("*")
      .eq("id", id)
      .maybeSingle()
      .then(({ data }) => {
        setQuote(data);
        setEmail(data?.email || "");
      });
    supabase
      .from("commandes")
      .select("*")
      .eq("proforma_id", id)
      .maybeSingle()
      .then(({ data }) => setCommande(data));
  }, [id]);

  async function handleSendEmail(e) {
    e.preventDefault();
    setSending(true);
    setEmailStatus(null);

    const { data: sessionData } = await supabase.auth.getSession();
    const token = sessionData.session?.access_token;

    try {
      const res = await fetch("/api/admin/send-invoice-email", {
        method: "POST",
        headers: { "Content-Type": "application/json", Authorization: `Bearer ${token}` },
        body: JSON.stringify({ quoteId: id, email }),
      });
      const body = await res.json();
      if (!res.ok) throw new Error(body.error);
      setEmailStatus({ type: "success", message: "Email envoyé avec succès." });
    } catch (err) {
      setEmailStatus({ type: "error", message: err.message });
    } finally {
      setSending(false);
    }
  }

  if (!quote) {
    return <p className="text-muted-foreground">Chargement...</p>;
  }

  const invoiceUrl = `/facture-proforma?id=${id}${commande ? "&definitive=true" : ""}`;
  const whatsappMessage = `Bonjour ${quote.nom || ""}, voici le lien vers votre facture Sierra Logistics : ${
    typeof window !== "undefined" ? window.location.origin : ""
  }${invoiceUrl}`;

  return (
    <div>
      <Link href="/admin/factures" className="text-sm font-semibold text-brand-accent hover:underline">
        ← Retour aux factures
      </Link>

      <div className="mt-4 flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-primary">{quote.nom || "Client"}</h1>
          <p className="text-sm text-muted-foreground">
            {quote.ville_depart} → {quote.ville_arrivee} · {quote.telephone}
          </p>
        </div>
        <Link
          href={invoiceUrl}
          target="_blank"
          className="inline-flex items-center gap-1.5 rounded-md border px-4 py-2 text-sm font-semibold text-primary hover:bg-muted"
        >
          <ExternalLink className="size-4" />
          Ouvrir la facture
        </Link>
      </div>

      <div className="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div className="rounded-lg bg-background p-5 shadow-sm">
          <h2 className="mb-3 font-semibold text-primary">Détails</h2>
          <dl className="space-y-2 text-sm">
            <div className="flex justify-between">
              <dt className="text-muted-foreground">Statut</dt>
              <dd className="font-semibold">{quote.statut === "commandé" ? "Commandé" : "En attente"}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="text-muted-foreground">Distance</dt>
              <dd>{quote.distance ? `${formatNumber(quote.distance)} km` : "-"}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="text-muted-foreground">Type de marchandise</dt>
              <dd>{quote.type_marchandise || "-"}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="text-muted-foreground">Poids</dt>
              <dd>{quote.poids ? `${formatNumber(quote.poids)} kg` : "-"}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="text-muted-foreground">Type de camion</dt>
              <dd>{quote.type_vehicle || "-"}</dd>
            </div>
            <div className="flex justify-between border-t pt-2 font-bold text-primary">
              <dt>Total</dt>
              <dd>{formatNumber(quote.total)} FCFA</dd>
            </div>
          </dl>

          {commande && (
            <>
              <h2 className="mt-5 mb-3 font-semibold text-primary">Exécution</h2>
              <dl className="space-y-2 text-sm">
                <div className="flex justify-between">
                  <dt className="text-muted-foreground">Camion</dt>
                  <dd>{commande.camion_immatriculation}</dd>
                </div>
                <div className="flex justify-between">
                  <dt className="text-muted-foreground">Chauffeur</dt>
                  <dd>{commande.chauffeur}</dd>
                </div>
              </dl>
            </>
          )}
        </div>

        <div className="space-y-6">
          <div className="rounded-lg bg-background p-5 shadow-sm">
            <h2 className="mb-3 flex items-center gap-2 font-semibold text-primary">
              <Mail className="size-4" /> Envoyer par email
            </h2>
            <form onSubmit={handleSendEmail} className="space-y-3">
              <div className="space-y-2">
                <Label htmlFor="email">Adresse email</Label>
                <Input
                  id="email"
                  type="email"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                />
              </div>
              <Button
                type="submit"
                disabled={sending}
                className="bg-brand-accent text-brand-accent-foreground hover:bg-brand-accent/90"
              >
                {sending ? "Envoi..." : "Envoyer l'email"}
              </Button>
            </form>
            {emailStatus && (
              <p
                className={`mt-3 text-sm font-medium ${
                  emailStatus.type === "success" ? "text-green-600" : "text-red-600"
                }`}
              >
                {emailStatus.message}
              </p>
            )}
          </div>

          <div className="rounded-lg bg-background p-5 shadow-sm">
            <h2 className="mb-3 flex items-center gap-2 font-semibold text-primary">
              <MessageCircle className="size-4" /> Envoyer par WhatsApp
            </h2>
            <p className="mb-3 text-sm text-muted-foreground">
              Ouvre WhatsApp avec un message pré-rempli contenant le lien de la facture, à
              destination de {quote.telephone || "ce client"}.
            </p>
            <a
              href={toWhatsAppLink(quote.telephone, whatsappMessage)}
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-2 rounded-md bg-[#25D366] px-4 py-2 text-sm font-semibold text-white transition-opacity hover:opacity-90"
            >
              <MessageCircle className="size-4" />
              Ouvrir WhatsApp
            </a>
          </div>
        </div>
      </div>
    </div>
  );
}
