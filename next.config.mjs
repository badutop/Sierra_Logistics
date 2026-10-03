/** @type {import('next').NextConfig} */
const nextConfig = {
  // Export statique : le front est déployé en fichiers HTML/JS/CSS dans
  // www/ sur l'hébergement mutualisé OVH, sans serveur Node (voir
  // migration/DEPLOIEMENT.md). En conséquence, ni redirects()/rewrites()/
  // headers() ni les routes API ne sont utilisables ici : la redirection
  // /expedier -> /devis (ex-redirects() ci-dessous) et le blocage
  // d'indexation du sous-domaine temporaire vivent maintenant dans le
  // .htaccess généré par le script de déploiement.
  output: "export",
  images: {
    unoptimized: true,
  },
  turbopack: {
    root: import.meta.dirname,
  },
};

export default nextConfig;
