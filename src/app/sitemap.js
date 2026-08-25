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
  const baseUrl = "https://sierra-logistics.example.com";

  return ROUTES.map((route) => ({
    url: `${baseUrl}${route}`,
    lastModified: new Date(),
  }));
}
