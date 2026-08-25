export default function robots() {
  return {
    rules: {
      userAgent: "*",
      allow: "/",
      disallow: ["/facture-proforma", "/facture-definitive"],
    },
    sitemap: "https://sierra-logistics.example.com/sitemap.xml",
  };
}
