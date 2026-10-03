// Client pour l'API REST publique du plugin WordPress (sierra/v1).
//
// Chemin relatif par défaut : le front et WordPress sont servis par le même
// domaine (WordPress dans /gestion/), donc aucune URL absolue n'est jamais
// codée en dur ici (voir migration/DEPLOIEMENT.md - "zéro URL en dur"). Une
// base différente peut être fournie via NEXT_PUBLIC_API_URL si le front et
// l'API ne sont finalement pas sur le même domaine.
const DEFAULT_BASE_URL = "/gestion/wp-json/sierra/v1";

function baseUrl() {
  return process.env.NEXT_PUBLIC_API_URL || DEFAULT_BASE_URL;
}

async function request(path, options = {}) {
  let response;
  try {
    response = await fetch(`${baseUrl()}${path}`, {
      ...options,
      headers: {
        "Content-Type": "application/json",
        ...options.headers,
      },
    });
  } catch {
    throw new ApiError("Impossible de contacter le serveur. Vérifiez votre connexion.");
  }

  const contentType = response.headers.get("content-type") || "";
  const body = contentType.includes("application/json") ? await response.json() : null;

  if (!response.ok) {
    throw new ApiError(
      body?.message || "Une erreur est survenue.",
      response.status,
      body?.data?.errors
    );
  }

  return body;
}

export class ApiError extends Error {
  constructor(message, status, fieldErrors) {
    super(message);
    this.status = status;
    this.fieldErrors = fieldErrors || null;
  }
}

export function createQuote(payload) {
  return request("/quotes", { method: "POST", body: JSON.stringify(payload) });
}

export function getQuote(id) {
  return request(`/quotes/${encodeURIComponent(id)}`);
}

export function lookupQuoteByTelephone(telephone) {
  return request(`/quotes/lookup?telephone=${encodeURIComponent(telephone)}`);
}

export function getCommandeByProforma(proformaId) {
  return request(`/commandes/${encodeURIComponent(proformaId)}`);
}

export function registerVehicle(payload) {
  return request("/vehicles", { method: "POST", body: JSON.stringify(payload) });
}
