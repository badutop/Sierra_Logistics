import { Phone, Mail, MapPin } from "lucide-react";

const CONTACT_INFO = [
  { icon: Phone, value: "+221 77 143 71 71", href: "tel:+221771437171" },
  {
    icon: Mail,
    value: "contact@sierra-logistics.com",
    href: "mailto:contact@sierra-logistics.com",
  },
  {
    icon: MapPin,
    value: "Diamniadio, Sénégal",
    href: "https://www.google.com/maps/search/?api=1&query=Diamniadio+S%C3%A9n%C3%A9gal",
  },
];

export function SiteFooter() {
  return (
    <footer className="text-sm text-neutral-100">
      <div className="flex flex-wrap items-center justify-center gap-x-6 gap-y-1.5 bg-brand-accent px-4 py-2.5 text-center text-white">
        {CONTACT_INFO.map((item) => (
          <a
            key={item.value}
            href={item.href}
            target={item.href.startsWith("http") ? "_blank" : undefined}
            rel={item.href.startsWith("http") ? "noopener noreferrer" : undefined}
            className="inline-flex items-center gap-1.5 font-medium transition-opacity hover:opacity-80"
          >
            <item.icon className="size-4" />
            {item.value}
          </a>
        ))}
        <span className="hidden sm:inline">•</span>
        <p>&copy; {new Date().getFullYear()} Sierra Logistics. Tous droits réservés.</p>
      </div>
      <div className="bg-neutral-600 py-3 text-center">
        <div className="mx-auto w-[90%] max-w-6xl space-x-2.5">
          <a href="#" className="text-neutral-100 transition-colors hover:text-brand-accent">
            Politique de confidentialité
          </a>
          <span>|</span>
          <a href="#" className="text-neutral-100 transition-colors hover:text-brand-accent">
            Termes et Conditions
          </a>
        </div>
      </div>
    </footer>
  );
}
