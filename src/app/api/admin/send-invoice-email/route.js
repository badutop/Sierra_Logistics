import { NextResponse } from "next/server";
import { Resend } from "resend";
import { getSupabaseAdmin } from "@/lib/supabaseAdmin";
import { formatNumber } from "@/lib/pricing";

async function requireAdmin(request) {
  const authHeader = request.headers.get("authorization") || "";
  const token = authHeader.replace(/^Bearer\s+/i, "");
  if (!token) return null;

  const supabaseAdmin = getSupabaseAdmin();
  const { data: userData, error: userError } = await supabaseAdmin.auth.getUser(token);
  if (userError || !userData?.user) return null;

  const { data: adminRow } = await supabaseAdmin
    .from("admins")
    .select("id")
    .eq("id", userData.user.id)
    .maybeSingle();

  return adminRow ? userData.user : null;
}

export async function POST(request) {
  const admin = await requireAdmin(request);
  if (!admin) {
    return NextResponse.json({ error: "Non autorisé." }, { status: 401 });
  }

  if (!process.env.RESEND_API_KEY) {
    return NextResponse.json(
      { error: "L'envoi d'email n'est pas configuré (RESEND_API_KEY manquante)." },
      { status: 500 }
    );
  }

  const { quoteId, email } = await request.json();
  if (!quoteId || !email) {
    return NextResponse.json({ error: "quoteId et email sont requis." }, { status: 400 });
  }

  const supabaseAdmin = getSupabaseAdmin();
  const { data: quote, error: quoteError } = await supabaseAdmin
    .from("quotes")
    .select("*")
    .eq("id", quoteId)
    .maybeSingle();

  if (quoteError || !quote) {
    return NextResponse.json({ error: "Devis introuvable." }, { status: 404 });
  }

  const invoiceUrl = `${new URL(request.url).origin}/facture-proforma?id=${quoteId}`;
  const isDefinitive = quote.statut === "commandé";

  const resend = new Resend(process.env.RESEND_API_KEY);
  const fromAddress = process.env.RESEND_FROM_EMAIL || "Sierra Logistics <onboarding@resend.dev>";

  try {
    const { error: sendError } = await resend.emails.send({
      from: fromAddress,
      to: email,
      subject: `${isDefinitive ? "Facture définitive" : "Facture proforma"} - Sierra Logistics`,
      html: `
        <div style="font-family: Arial, sans-serif; max-width: 560px; margin: 0 auto; color: #1a1a1a;">
          <div style="background: #111111; padding: 20px 24px;">
            <span style="color: #73ba09; font-size: 20px; font-weight: bold;">Sierra Logistics</span>
          </div>
          <div style="padding: 24px; border: 1px solid #e5e5e5; border-top: none;">
            <p>Bonjour ${quote.nom || ""},</p>
            <p>
              Veuillez trouver ci-dessous le lien vers votre
              ${isDefinitive ? "facture définitive" : "facture proforma"}
              pour le trajet <strong>${quote.ville_depart} → ${quote.ville_arrivee}</strong>.
            </p>
            <table style="width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 14px;">
              <tr><td style="padding: 4px 0; color: #555;">Type de marchandise</td><td style="padding: 4px 0; text-align: right;">${quote.type_marchandise || "-"}</td></tr>
              <tr><td style="padding: 4px 0; color: #555;">Poids</td><td style="padding: 4px 0; text-align: right;">${quote.poids ? `${formatNumber(quote.poids)} kg` : "-"}</td></tr>
              <tr><td style="padding: 8px 0; font-weight: bold; border-top: 1px solid #e5e5e5;">Total</td><td style="padding: 8px 0; text-align: right; font-weight: bold; border-top: 1px solid #e5e5e5;">${formatNumber(quote.total)} FCFA</td></tr>
            </table>
            <a href="${invoiceUrl}" style="display: inline-block; background: #73ba09; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold;">
              Voir la facture
            </a>
            <p style="margin-top: 24px; font-size: 13px; color: #777;">
              Sierra Logistics — Diamniadio, Sénégal — +221 77 143 71 71
            </p>
          </div>
        </div>
      `,
    });

    if (sendError) {
      return NextResponse.json({ error: sendError.message }, { status: 500 });
    }
  } catch (err) {
    return NextResponse.json({ error: err.message }, { status: 500 });
  }

  return NextResponse.json({ success: true });
}
