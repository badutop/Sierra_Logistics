"use client";

import { useState } from "react";
import Link from "next/link";
import Image from "next/image";
import { ChevronDown, Menu, X } from "lucide-react";
import { cn } from "@/lib/utils";

const NAV_LINKS = [
  { href: "/#hero", label: "Accueil" },
  {
    href: "/#services",
    label: "Nos Services",
    items: [
      { href: "/transport-routier", label: "Transport Routier" },
      { href: "/transport-conteneurs", label: "Transport de Conteneurs" },
      { href: "/location-camions", label: "Location de Camions" },
      { href: "/bennes-tp", label: "Bennes TP" },
    ],
  },
  {
    href: "/#realisations",
    label: "Nos Réalisations",
    items: [
      { href: "/realisations/transfert-cereales", label: "Transfert de Céréales" },
      { href: "/realisations/empotage-ble", label: "Empotage de Blé (Dakar)" },
      { href: "/realisations/manutention-ter", label: "Manutention & Levage (TER)" },
      { href: "/realisations/transport-arachide", label: "Campagne Arachidière" },
    ],
  },
  {
    href: "/#pourquoi",
    label: "Pourquoi Nous ?",
    items: [
      { href: "/fiabilite", label: "Fiabilité & Sécurité" },
      { href: "/technologie", label: "Haute Technologie" },
      { href: "/support-client", label: "Support Client" },
    ],
  },
];

const CLIENT_LINKS = [
  { href: "/inscription-camion", label: "Inscrire son Camion" },
  { href: "/devis", label: "Demander un Devis", primary: true },
  { href: "/commander", label: "Commander un Camion" },
  { href: "/suivi-flotte", label: "Suivre sa Flotte" },
];

const NAV_LINK_CLASS =
  "relative font-semibold text-muted-foreground transition-colors hover:text-brand-accent after:absolute after:-bottom-1 after:left-0 after:h-0.5 after:w-0 after:bg-brand-accent after:transition-all after:duration-300 hover:after:w-full";

export function SiteHeader() {
  const [mobileOpen, setMobileOpen] = useState(false);
  const [openMobileMenu, setOpenMobileMenu] = useState(null);

  return (
    <header className="fixed top-0 left-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80">
      <nav className="mx-auto flex w-[90%] max-w-6xl items-center justify-between py-0.5">
        <Link href="/" className="flex items-center gap-2">
          <Image
            src="/images/sierra-logistics-logo-header.png"
            alt="Sierra Logistics"
            width={187}
            height={95}
            className="h-14 w-auto object-contain"
            priority
          />
          <span className="hidden text-sm font-medium italic text-muted-foreground sm:inline">
            Donnez nous la destination
          </span>
        </Link>

        <ul className="hidden items-center gap-8 md:flex">
          {NAV_LINKS.map((link) =>
            link.items ? (
              <li key={link.href} className="group relative">
                <Link href={link.href} className={cn(NAV_LINK_CLASS, "inline-flex items-center gap-1")}>
                  {link.label}
                  <ChevronDown className="size-3.5" />
                </Link>
                <ul className="invisible absolute left-0 top-full min-w-[260px] rounded-md border bg-background py-2 opacity-0 shadow-lg transition-opacity group-hover:visible group-hover:opacity-100">
                  {link.items.map((item) => (
                    <li key={item.href}>
                      <Link
                        href={item.href}
                        className="block px-4 py-2 text-sm leading-snug font-medium whitespace-normal text-muted-foreground hover:bg-muted hover:text-brand-accent"
                      >
                        {item.label}
                      </Link>
                    </li>
                  ))}
                </ul>
              </li>
            ) : (
              <li key={link.href}>
                <Link href={link.href} className={NAV_LINK_CLASS}>
                  {link.label}
                </Link>
              </li>
            )
          )}
          <li className="group relative">
            <button className="flex items-center gap-1 font-semibold text-muted-foreground transition-colors group-hover:text-brand-accent">
              Espace Client <ChevronDown className="size-3.5" />
            </button>
            <ul className="invisible absolute left-0 top-full min-w-[220px] rounded-md border bg-background py-2 opacity-0 shadow-lg transition-opacity group-hover:visible group-hover:opacity-100">
              {CLIENT_LINKS.map((link) => (
                <li key={link.href} className={link.primary ? "mt-1 px-2 pb-1 pt-1" : ""}>
                  <Link
                    href={link.href}
                    className={
                      link.primary
                        ? "block whitespace-nowrap rounded-md bg-brand-accent px-3 py-2 text-center text-sm font-semibold text-brand-accent-foreground transition-opacity hover:opacity-90"
                        : "block whitespace-nowrap px-4 py-2 text-sm font-medium text-muted-foreground hover:bg-muted hover:text-brand-accent"
                    }
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </li>
        </ul>

        <button
          aria-label="Ouvrir le menu"
          className="flex size-10 items-center justify-center rounded-md bg-brand-accent text-brand-accent-foreground transition-opacity hover:opacity-90 md:hidden"
          onClick={() => setMobileOpen((v) => !v)}
        >
          {mobileOpen ? <X className="size-6" /> : <Menu className="size-6" />}
        </button>
      </nav>

      {mobileOpen && (
        <ul className="flex flex-col border-t bg-background md:hidden">
          {NAV_LINKS.map((link) =>
            link.items ? (
              <li key={link.href} className="border-b">
                <div className="flex items-center justify-between">
                  <Link
                    href={link.href}
                    className="flex-1 px-5 py-3 font-semibold text-muted-foreground hover:text-brand-accent"
                    onClick={() => setMobileOpen(false)}
                  >
                    {link.label}
                  </Link>
                  <button
                    aria-label={`Afficher les pages ${link.label}`}
                    className="px-4 py-3 text-muted-foreground"
                    onClick={() =>
                      setOpenMobileMenu((k) => (k === link.href ? null : link.href))
                    }
                  >
                    <ChevronDown
                      className={cn(
                        "size-4 transition-transform",
                        openMobileMenu === link.href && "rotate-180"
                      )}
                    />
                  </button>
                </div>
                {openMobileMenu === link.href && (
                  <ul className="space-y-1 bg-muted p-3">
                    {link.items.map((item) => (
                      <li key={item.href}>
                        <Link
                          href={item.href}
                          className="block rounded-md px-4 py-2 text-sm leading-snug font-medium text-muted-foreground hover:bg-background hover:text-brand-accent"
                          onClick={() => setMobileOpen(false)}
                        >
                          {item.label}
                        </Link>
                      </li>
                    ))}
                  </ul>
                )}
              </li>
            ) : (
              <li key={link.href}>
                <Link
                  href={link.href}
                  className="block border-b px-5 py-3 font-semibold text-muted-foreground hover:text-brand-accent"
                  onClick={() => setMobileOpen(false)}
                >
                  {link.label}
                </Link>
              </li>
            )
          )}
          <li>
            <button
              className="flex w-full items-center justify-between px-5 py-3 font-semibold text-muted-foreground"
              onClick={() => setOpenMobileMenu((k) => (k === "client" ? null : "client"))}
            >
              Espace Client
              <ChevronDown
                className={cn(
                  "size-4 transition-transform",
                  openMobileMenu === "client" && "rotate-180"
                )}
              />
            </button>
            {openMobileMenu === "client" && (
              <ul className="space-y-2 bg-muted p-3">
                {CLIENT_LINKS.map((link) => (
                  <li key={link.href}>
                    <Link
                      href={link.href}
                      className={
                        link.primary
                          ? "block rounded-md bg-brand-accent px-4 py-2.5 text-center text-sm font-semibold text-brand-accent-foreground"
                          : "block rounded-md px-4 py-2.5 text-sm font-medium text-muted-foreground hover:bg-background hover:text-brand-accent"
                      }
                      onClick={() => setMobileOpen(false)}
                    >
                      {link.label}
                    </Link>
                  </li>
                ))}
              </ul>
            )}
          </li>
        </ul>
      )}
    </header>
  );
}
