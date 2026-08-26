"use client";

import { useState } from "react";
import Link from "next/link";
import Image from "next/image";
import { ChevronDown, Menu, X } from "lucide-react";
import { cn } from "@/lib/utils";

const CLIENT_LINKS = [
  { href: "/inscription-camion", label: "Inscrire son Camion" },
  { href: "/devis", label: "Demander un Devis" },
  { href: "/commander", label: "Commander un Camion" },
  { href: "/suivi-flotte", label: "Suivre sa Flotte" },
];

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
        </Link>

        <ul className="hidden items-center gap-8 md:flex">
          <li>
            <Link href="/#hero" className="font-semibold text-muted-foreground transition-colors hover:text-primary">
              Accueil
            </Link>
          </li>
          <li>
            <Link href="/#services" className="font-semibold text-muted-foreground transition-colors hover:text-primary">
              Nos Services
            </Link>
          </li>
          <li>
            <Link href="/#pourquoi" className="font-semibold text-muted-foreground transition-colors hover:text-primary">
              Pourquoi Nous ?
            </Link>
          </li>
          <li className="group relative">
            <button className="flex items-center gap-1 font-semibold text-muted-foreground transition-colors group-hover:text-primary">
              Espace Client <ChevronDown className="size-3.5" />
            </button>
            <ul className="invisible absolute left-0 top-full min-w-[220px] rounded-md border bg-background py-2 opacity-0 shadow-lg transition-opacity group-hover:visible group-hover:opacity-100">
              {CLIENT_LINKS.map((link) => (
                <li key={link.href}>
                  <Link
                    href={link.href}
                    className="block whitespace-nowrap px-4 py-2 text-sm font-medium text-muted-foreground hover:bg-muted hover:text-primary"
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
          <li>
            <Link
              href="/#hero"
              className="block border-b px-5 py-3 font-semibold text-muted-foreground"
              onClick={() => setMobileOpen(false)}
            >
              Accueil
            </Link>
          </li>
          <li>
            <Link
              href="/#services"
              className="block border-b px-5 py-3 font-semibold text-muted-foreground"
              onClick={() => setMobileOpen(false)}
            >
              Nos Services
            </Link>
          </li>
          <li>
            <Link
              href="/#pourquoi"
              className="block border-b px-5 py-3 font-semibold text-muted-foreground"
              onClick={() => setMobileOpen(false)}
            >
              Pourquoi Nous ?
            </Link>
          </li>
          <li>
            <button
              className="flex w-full items-center justify-between px-5 py-3 font-semibold text-muted-foreground"
              onClick={() => setDropdownOpen((v) => !v)}
            >
              Espace Client
              <ChevronDown className={cn("size-4 transition-transform", dropdownOpen && "rotate-180")} />
            </button>
            {dropdownOpen && (
              <ul className="bg-muted">
                {CLIENT_LINKS.map((link) => (
                  <li key={link.href} className="border-b border-border/60 last:border-none">
                    <Link
                      href={link.href}
                      className="block px-8 py-2.5 text-sm font-medium text-muted-foreground"
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
