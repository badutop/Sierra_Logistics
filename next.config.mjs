/** @type {import('next').NextConfig} */
const nextConfig = {
  turbopack: {
    root: import.meta.dirname,
  },
  async redirects() {
    return [
      // expedier.html était une variante divergente (schéma CEDEAO) du même
      // formulaire de devis, jamais reliée que depuis un seul lien de nav
      // ("Demander un Devis" sur fiabilite.html) - consolidée sur /devis.
      {
        source: "/expedier",
        destination: "/devis",
        permanent: true,
      },
    ];
  },
};

export default nextConfig;
