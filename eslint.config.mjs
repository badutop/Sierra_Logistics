import { defineConfig, globalIgnores } from "eslint/config";
import nextVitals from "eslint-config-next/core-web-vitals";

const eslintConfig = defineConfig([
  ...nextVitals,
  // Override default ignores of eslint-config-next.
  globalIgnores([
    // Default ignores of eslint-config-next:
    ".next/**",
    "out/**",
    "build/**",
    "next-env.d.ts",
    // Projet PHP séparé (plugin/thème WordPress), avec son propre outillage
    // (PHPCS/PHPUnit, voir wordpress/wp-content/plugins/sierra-logistics-core).
    "wordpress/**",
  ]),
]);

export default eslintConfig;
