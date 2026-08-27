"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { FileText, Clock, Wallet, Truck, Package, Users } from "lucide-react";
import { supabase } from "@/lib/supabaseClient";
import { formatNumber } from "@/lib/pricing";
import { StatCard } from "@/components/admin/stat-card";

export default function AdminDashboardPage() {
  const [stats, setStats] = useState(null);
  const [recentQuotes, setRecentQuotes] = useState([]);

  useEffect(() => {
    (async () => {
      const [{ data: quotes }, { data: vehicles }, { data: commandes }] = await Promise.all([
        supabase.from("quotes").select("id, statut, total, created_at, nom, ville_depart, ville_arrivee").order("created_at", { ascending: false }),
        supabase.from("vehicles").select("id, status"),
        supabase.from("commandes").select("id"),
      ]);

      const quotesList = quotes || [];
      const enAttente = quotesList.filter((q) => q.statut === "en_attente").length;
      const commandees = quotesList.filter((q) => q.statut === "commandé").length;
      const chiffreAffaires = quotesList
        .filter((q) => q.statut === "commandé")
        .reduce((sum, q) => sum + (q.total || 0), 0);

      setStats({
        totalQuotes: quotesList.length,
        enAttente,
        commandees,
        chiffreAffaires,
        totalVehicles: (vehicles || []).length,
        disponibles: (vehicles || []).filter((v) => v.status === "disponible").length,
        totalCommandes: (commandes || []).length,
      });
      setRecentQuotes(quotesList.slice(0, 8));
    })();
  }, []);

  return (
    <div>
      <h1 className="text-2xl font-bold text-primary">Tableau de bord</h1>
      <p className="mt-1 text-sm text-muted-foreground">Vue d&apos;ensemble de l&apos;activité Sierra Logistics.</p>

      <div className="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <StatCard icon={FileText} label="Devis reçus" value={stats ? formatNumber(stats.totalQuotes) : "—"} />
        <StatCard icon={Clock} label="Devis en attente" value={stats ? formatNumber(stats.enAttente) : "—"} />
        <StatCard icon={Package} label="Commandes validées" value={stats ? formatNumber(stats.totalCommandes) : "—"} />
        <StatCard
          icon={Wallet}
          label="Chiffre d'affaires (validé)"
          value={stats ? `${formatNumber(stats.chiffreAffaires)} FCFA` : "—"}
        />
        <StatCard icon={Truck} label="Camions enregistrés" value={stats ? formatNumber(stats.totalVehicles) : "—"} />
        <StatCard
          icon={Users}
          label="Camions disponibles"
          value={stats ? formatNumber(stats.disponibles) : "—"}
          hint={stats ? `${formatNumber(stats.totalVehicles - stats.disponibles)} en course ou en maintenance` : undefined}
        />
      </div>

      <div className="mt-8 rounded-lg bg-background shadow-sm">
        <div className="flex items-center justify-between border-b p-4">
          <h2 className="font-semibold text-primary">Derniers devis</h2>
          <Link href="/admin/factures" className="text-sm font-semibold text-brand-accent hover:underline">
            Voir toutes les factures
          </Link>
        </div>
        <div className="divide-y">
          {recentQuotes.length === 0 && (
            <p className="p-4 text-sm text-muted-foreground">Aucun devis pour le moment.</p>
          )}
          {recentQuotes.map((q) => (
            <Link
              key={q.id}
              href={`/admin/factures/${q.id}`}
              className="flex items-center justify-between p-4 text-sm hover:bg-muted"
            >
              <div>
                <p className="font-semibold text-primary">{q.nom || "Client"}</p>
                <p className="text-muted-foreground">
                  {q.ville_depart} → {q.ville_arrivee}
                </p>
              </div>
              <div className="text-right">
                <p className="font-mono font-semibold">{formatNumber(q.total)} FCFA</p>
                <span
                  className={`text-xs font-semibold ${
                    q.statut === "commandé" ? "text-green-600" : "text-orange-500"
                  }`}
                >
                  {q.statut === "commandé" ? "Commandé" : "En attente"}
                </span>
              </div>
            </Link>
          ))}
        </div>
      </div>
    </div>
  );
}
