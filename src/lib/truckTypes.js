// Vocabulaire unique partagé entre le formulaire de devis (calcul tarifaire,
// voir pricing.js) et le formulaire d'inscription de camion. Avant, les deux
// utilisaient des listes différentes et non mappables, ce qui rendait
// impossible de faire correspondre un devis à un camion du bon type.
export const TRUCK_TYPES = [
  "Camion benne",
  "Camion frigorifique",
  "Camion bâché",
  "Camion citerne",
  "Plateau",
  "Fourgon",
];
