// Requis par l'export statique, voir le même commentaire dans sitemap.js.
export const dynamic = "force-static";

export default function robots() {
  // Jamais de domaine en dur : voir la même règle dans layout.js et sitemap.js.
  const baseUrl = process.env.NEXT_PUBLIC_SITE_URL || "https://sierra-logistics-web.vercel.app";
  // "false" uniquement : sous-domaine temporaire avant bascule sur le domaine
  // définitif (voir migration/DEPLOIEMENT.md, section "Bascule vers le domaine
  // définitif"). Absent ou "true" ailleurs, notamment en production actuelle.
  const allowIndexing = process.env.NEXT_PUBLIC_ALLOW_INDEXING !== "false";

  return {
    rules: {
      userAgent: "*",
      disallow: allowIndexing ? ["/facture-proforma", "/facture-definitive"] : "/",
    },
    sitemap: `${baseUrl}/sitemap.xml`,
  };
}
