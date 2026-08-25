import villesSenegal from "@/data/villes-senegal.json";

const ZONES_TARIFAIRES = [
  { min: 1, max: 100, tarif: 500, libelle: "Zone 1 (1-100 km)" },
  { min: 101, max: 300, tarif: 700, libelle: "Zone 2 (101-300 km)" },
  { min: 301, max: 500, tarif: 800, libelle: "Zone 3 (301-500 km)" },
  { min: 501, max: 750, tarif: 900, libelle: "Zone 4 (501-750 km)" },
  { min: 751, max: Infinity, tarif: 1000, libelle: "Zone 5 (751+ km)" },
];

const COEFFICIENTS_CAMION = {
  "Camion benne": 1.0,
  "Camion frigorifique": 1.7,
  "Camion bâché": 1.2,
  "Camion citerne": 1.5,
  Plateau: 1.3,
  Fourgon: 1.1,
};

export const VILLES_SENEGAL = villesSenegal.departmental_capitals;

const normaliser = (nom) =>
  nom
    .toLowerCase()
    .normalize("NFD")
    .replace(/[̀-ͯ]/g, "")
    .trim();

export function calculerDistance(villeDepart, villeArrivee) {
  const villeDep = VILLES_SENEGAL.find(
    (v) => normaliser(v.name) === normaliser(villeDepart)
  );
  const villeArr = VILLES_SENEGAL.find(
    (v) => normaliser(v.name) === normaliser(villeArrivee)
  );

  if (!villeDep) throw new Error(`Ville de départ "${villeDepart}" non trouvée`);
  if (!villeArr) throw new Error(`Ville d'arrivée "${villeArrivee}" non trouvée`);

  return Math.abs(villeArr.distance - villeDep.distance);
}

export function determinerZone(distance) {
  return (
    ZONES_TARIFAIRES.find((z) => distance >= z.min && distance <= z.max) ||
    ZONES_TARIFAIRES[ZONES_TARIFAIRES.length - 1]
  );
}

export function calculerDevis({ villeDepart, villeArrivee, typeCamion }) {
  const distance = calculerDistance(villeDepart, villeArrivee);
  const zone = determinerZone(distance);
  const coefficient = COEFFICIENTS_CAMION[typeCamion] || 1.0;
  const montantTransport = zone.tarif * distance;
  const majoration = (coefficient - 1) * zone.tarif * distance;
  const sousTotal = montantTransport + majoration;
  const tva = sousTotal * 0.18;
  const total = sousTotal + tva;

  return {
    distance: Math.round(distance),
    zone: zone.libelle,
    tarifZone: zone.tarif,
    coefficientCamion: coefficient,
    montantTransport: Math.round(montantTransport),
    majoration: Math.round(majoration),
    sousTotal: Math.round(sousTotal),
    tva: Math.round(tva),
    total: Math.round(total),
  };
}

export function formatNumber(num) {
  return new Intl.NumberFormat("fr-FR").format(num || 0);
}
