"use client";

import { useState } from "react";
import Link from "next/link";
import Image from "next/image";
import { usePathname, useRouter } from "next/navigation";
import { LayoutDashboard, FileText, Truck, LogOut, Menu, X } from "lucide-react";
import { supabase } from "@/lib/supabaseClient";
import { cn } from "@/lib/utils";

const NAV_ITEMS = [
  { href: "/admin", label: "Tableau de bord", icon: LayoutDashboard },
  { href: "/admin/factures", label: "Factures", icon: FileText },
  { href: "/admin/commandes", label: "Commandes", icon: Truck },
];

export function AdminShell({ admin, children }) {
  const pathname = usePathname();
  const router = useRouter();
  const [mobileOpen, setMobileOpen] = useState(false);

  async function handleLogout() {
    await supabase.auth.signOut();
    router.push("/admin/login");
  }

  return (
    <div className="flex min-h-full">
      <aside className="hidden w-64 shrink-0 flex-col bg-primary text-white md:flex">
        <div className="flex items-center gap-2 border-b border-white/10 px-6 py-5">
          <Image
            src="/images/sierra-logistics-logo-header.png"
            alt="Sierra Logistics"
            width={187}
            height={95}
            className="h-9 w-auto object-contain"
          />
          <span className="text-sm font-semibold">Admin</span>
        </div>
        <nav className="flex-1 space-y-1 px-3 py-6">
          {NAV_ITEMS.map((item) => {
            const active = pathname === item.href || pathname.startsWith(`${item.href}/`);
            return (
              <Link
                key={item.href}
                href={item.href}
                className={cn(
                  "flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-semibold transition-colors",
                  active
                    ? "bg-brand-accent text-brand-accent-foreground"
                    : "text-white/80 hover:bg-white/10 hover:text-white"
                )}
              >
                <item.icon className="size-4.5" />
                {item.label}
              </Link>
            );
          })}
        </nav>
        <div className="border-t border-white/10 p-4">
          <p className="truncate px-3 text-xs text-white/60">{admin?.name || "Compte"}</p>
          <button
            onClick={handleLogout}
            className="mt-2 flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-sm font-semibold text-white/80 transition-colors hover:bg-white/10 hover:text-white"
          >
            <LogOut className="size-4.5" />
            Déconnexion
          </button>
        </div>
      </aside>

      <div className="flex min-h-full flex-1 flex-col">
        <header className="flex items-center justify-between border-b bg-background px-4 py-3 md:hidden">
          <span className="font-bold text-primary">Sierra Logistics — Admin</span>
          <button
            aria-label="Menu"
            className="flex size-10 items-center justify-center rounded-md bg-brand-accent text-brand-accent-foreground transition-opacity hover:opacity-90"
            onClick={() => setMobileOpen((v) => !v)}
          >
            {mobileOpen ? <X className="size-6" /> : <Menu className="size-6" />}
          </button>
        </header>
        {mobileOpen && (
          <nav className="space-y-1 border-b bg-primary p-3 md:hidden">
            {NAV_ITEMS.map((item) => (
              <Link
                key={item.href}
                href={item.href}
                onClick={() => setMobileOpen(false)}
                className="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10"
              >
                <item.icon className="size-4.5" />
                {item.label}
              </Link>
            ))}
            <button
              onClick={handleLogout}
              className="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10"
            >
              <LogOut className="size-4.5" />
              Déconnexion
            </button>
          </nav>
        )}
        <main className="flex-1 p-4 md:p-8">{children}</main>
      </div>
    </div>
  );
}
