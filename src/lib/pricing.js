import villesSenegal from "@/data/villes-senegal.json";

// Le calcul tarifaire (zones, coefficients, TVA) est désormais fait
// exclusivement côté serveur par l'API sierra/v1 (voir
// wordpress/wp-content/plugins/sierra-logistics-core/includes/class-pricing.php) :
// le front n'a plus besoin de connaître les tarifs, seulement la liste des
// villes pour ses menus déroulants.
export const VILLES_SENEGAL = villesSenegal.departmental_capitals;

export function formatNumber(num) {
  return new Intl.NumberFormat("fr-FR").format(num || 0);
}
