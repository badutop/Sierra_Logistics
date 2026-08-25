import { NextResponse } from "next/server";
import { getSupabaseAdmin } from "@/lib/supabaseAdmin";

// La table `commandes` n'est plus lisible avec la clé anon (RLS) - cette
// route sert uniquement à afficher la commande déjà validée d'un devis sur
// la vue définitive de /facture-proforma.
export async function GET(request) {
  const proformaId = request.nextUrl.searchParams.get("proformaId");

  if (!proformaId) {
    return NextResponse.json({ error: "proformaId manquant" }, { status: 400 });
  }

  const { data: commande, error } = await getSupabaseAdmin()
    .from("commandes")
    .select("*")
    .eq("proforma_id", proformaId)
    .maybeSingle();

  if (error) {
    return NextResponse.json({ error: error.message }, { status: 500 });
  }

  return NextResponse.json({ commande });
}
