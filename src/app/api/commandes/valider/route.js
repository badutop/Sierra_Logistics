import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabaseAdmin";

// Réclame un véhicule "disponible" de façon atomique : l'UPDATE conditionnel
// (status='disponible' -> 'en_course') ne réussit que si personne d'autre ne
// l'a pris entre-temps, ce qui évite le double-booking en cas de validations
// concurrentes sur le même camion.
async function claimVehicle(candidates) {
  for (const vehicle of candidates) {
    const { data, error } = await supabaseAdmin
      .from("vehicles")
      .update({ status: "en_course" })
      .eq("id", vehicle.id)
      .eq("status", "disponible")
      .select()
      .maybeSingle();

    if (error) throw error;
    if (data) return data;
  }
  return null;
}

export async function POST(request) {
  const { quoteId } = await request.json();

  if (!quoteId) {
    return NextResponse.json({ error: "quoteId manquant" }, { status: 400 });
  }

  const { data: quote, error: quoteError } = await supabaseAdmin
    .from("quotes")
    .select("*")
    .eq("id", quoteId)
    .maybeSingle();

  if (quoteError) {
    return NextResponse.json({ error: quoteError.message }, { status: 500 });
  }
  if (!quote) {
    return NextResponse.json({ error: "Devis introuvable" }, { status: 404 });
  }
  if (quote.statut !== "en_attente") {
    return NextResponse.json(
      { error: "Ce devis a déjà été validé." },
      { status: 409 }
    );
  }

  const { data: available, error: vehiclesError } = await supabaseAdmin
    .from("vehicles")
    .select("*")
    .eq("status", "disponible");

  if (vehiclesError) {
    return NextResponse.json({ error: vehiclesError.message }, { status: 500 });
  }

  // Priorité aux camions du type demandé, puis n'importe quel disponible.
  const matching = available.filter((v) => v.model === quote.type_vehicle);
  const rest = available.filter((v) => v.model !== quote.type_vehicle);
  const candidates = [...matching, ...rest];

  const vehicle = await claimVehicle(candidates);

  if (!vehicle) {
    return NextResponse.json(
      { error: "Aucun camion disponible actuellement." },
      { status: 409 }
    );
  }

  const { data: commande, error: commandeError } = await supabaseAdmin
    .from("commandes")
    .insert({
      proforma_id: quoteId,
      vehicle_id: vehicle.id,
      camion_immatriculation: vehicle.license_plate,
      chauffeur: vehicle.name,
      telephone_chauffeur: vehicle.contact_phone,
    })
    .select()
    .single();

  if (commandeError) {
    // Libère le camion réservé si l'insertion de la commande échoue, pour ne
    // pas le laisser bloqué en "en_course" sans commande associée.
    await supabaseAdmin
      .from("vehicles")
      .update({ status: "disponible" })
      .eq("id", vehicle.id);

    return NextResponse.json({ error: commandeError.message }, { status: 500 });
  }

  const { error: updateQuoteError } = await supabaseAdmin
    .from("quotes")
    .update({ statut: "commandé" })
    .eq("id", quoteId);

  if (updateQuoteError) {
    return NextResponse.json({ error: updateQuoteError.message }, { status: 500 });
  }

  return NextResponse.json({ commande });
}
