"use client";

import { useState } from "react";
import Link from "next/link";
import Image from "next/image";
import { ChevronDown, Menu, X } from "lucide-react";
import { cn } from "@/lib/utils";

const NAV_LINKS = [
  { href: "/#hero", label: "Accueil" },
  { href: "/#services", label: "Nos Services" },
  { href: "/#realisations", label: "Nos Réalisations" },
  { href: "/#pourquoi", label: "Pourquoi Nous ?" },
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
  const [dropdownOpen, setDropdownOpen] = useState(false);

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
          {NAV_LINKS.map((link) => (
            <li key={link.href}>
              <Link href={link.href} className={NAV_LINK_CLASS}>
                {link.label}
              </Link>
            </li>
          ))}
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
          className="text-primary md:hidden"
          onClick={() => setMobileOpen((v) => !v)}
        >
          {mobileOpen ? <X className="size-7" /> : <Menu className="size-7" />}
        </button>
      </nav>

      {mobileOpen && (
        <ul className="flex flex-col border-t bg-background md:hidden">
          {NAV_LINKS.map((link) => (
            <li key={link.href}>
              <Link
                href={link.href}
                className="block border-b px-5 py-3 font-semibold text-muted-foreground hover:text-brand-accent"
                onClick={() => setMobileOpen(false)}
              >
                {link.label}
              </Link>
            </li>
          ))}
          <li>
            <button
              className="flex w-full items-center justify-between px-5 py-3 font-semibold text-muted-foreground"
              onClick={() => setDropdownOpen((v) => !v)}
            >
              Espace Client
              <ChevronDown className={cn("size-4 transition-transform", dropdownOpen && "rotate-180")} />
            </button>
            {dropdownOpen && (
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
