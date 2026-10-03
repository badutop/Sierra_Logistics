import { Poppins } from "next/font/google";
import "./globals.css";

const poppins = Poppins({
  variable: "--font-sans",
  subsets: ["latin"],
  weight: ["400", "600", "700"],
});

// NEXT_PUBLIC_SITE_URL doit être définie dans l'environnement de build (Vercel,
// ou .env lors d'un export statique) : jamais de domaine en dur ici, pour que
// la bascule sous-domaine temporaire -> domaine définitif ne touche qu'à la
// configuration. Le flag NEXT_PUBLIC_ALLOW_INDEXING="false" bloque l'indexation
// (sous-domaine temporaire) ; absent ou "true", l'indexation reste autorisée.
const siteUrl = process.env.NEXT_PUBLIC_SITE_URL || "https://sierra-logistics-web.vercel.app";
const allowIndexing = process.env.NEXT_PUBLIC_ALLOW_INDEXING !== "false";

export const metadata = {
  metadataBase: new URL(siteUrl),
  title: {
    default: "Sierra Logistics - Solutions de Transport et Logistique",
    template: "%s - Sierra Logistics",
  },
  description:
    "Sierra Logistics, votre partenaire de confiance pour le transport routier, l'entreposage et la distribution locale au Sénégal et en Afrique de l'Ouest.",
  openGraph: {
    type: "website",
    locale: "fr_FR",
    siteName: "Sierra Logistics",
  },
  robots: allowIndexing
    ? { index: true, follow: true }
    : { index: false, follow: false, nocache: true },
};

export default function RootLayout({ children }) {
  return (
    <html lang="fr" className={`${poppins.variable} h-full antialiased`}>
      <body className="flex min-h-full flex-col">{children}</body>
    </html>
  );
}
