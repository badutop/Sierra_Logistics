// Requis par l'export statique (output: "export" dans next.config.mjs) :
// sans ça, Next traite cette route comme dynamique à cause de la lecture de
// process.env ci-dessous, ce qui est incompatible avec un export de fichiers
// statiques (pas de serveur pour évaluer quoi que ce soit à la requête).
export const dynamic = "force-static";

const ROUTES = [
  "",
  "/fiabilite",
  "/technologie",
  "/support-client",
  "/transport-routier",
  "/entreposage",
  "/distribution-locale",
  "/transporteurs",
  "/inscription-camion",
  "/devis",
  "/commander",
  "/suivi-flotte",
];

export default function sitemap() {
  // Jamais de domaine en dur : voir la même règle dans layout.js et robots.js.
  const baseUrl = process.env.NEXT_PUBLIC_SITE_URL || "https://sierra-logistics-web.vercel.app";

  return ROUTES.map((route) => ({
    url: `${baseUrl}${route}`,
    lastModified: new Date(),
  }));
}
