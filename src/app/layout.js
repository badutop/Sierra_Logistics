import { Poppins } from "next/font/google";
import { SiteHeader } from "@/components/site-header";
import { SiteFooter } from "@/components/site-footer";
import "./globals.css";

const poppins = Poppins({
  variable: "--font-sans",
  subsets: ["latin"],
  weight: ["400", "600", "700"],
});

export const metadata = {
  metadataBase: new URL("https://sierra-logistics.example.com"),
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
};

export default function RootLayout({ children }) {
  return (
    <html lang="fr" className={`${poppins.variable} h-full antialiased`}>
      <body className="flex min-h-full flex-col">
        <SiteHeader />
        <main className="flex-1">{children}</main>
        <SiteFooter />
      </body>
    </html>
  );
}
