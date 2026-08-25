"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { supabase } from "@/lib/supabaseClient";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { FormMessage } from "@/components/form-message";

export function CommanderForm() {
  const router = useRouter();
  const [telephone, setTelephone] = useState("");
  const [searching, setSearching] = useState(false);
  const [error, setError] = useState(null);

  async function handleSubmit(e) {
    e.preventDefault();
    setError(null);

    const trimmed = telephone.trim();
    if (!trimmed) {
      setError("Veuillez saisir un numéro de téléphone valide.");
      return;
    }

    setSearching(true);
    const { data, error: searchError } = await supabase
      .from("quotes")
      .select("id")
      .eq("telephone", trimmed)
      .order("created_at", { ascending: false })
      .limit(1);
    setSearching(false);

    if (searchError) {
      setError(`Erreur lors de la recherche : ${searchError.message}`);
      return;
    }

    if (!data?.length) {
      setError("Aucun devis trouvé pour ce numéro.");
      return;
    }

    router.push(`/facture-proforma?id=${data[0].id}`);
  }

  return (
    <>
      <form
        onSubmit={handleSubmit}
        className="mt-8 space-y-5 rounded-2xl bg-[repeating-linear-gradient(45deg,#e0f0ff,#e0f0ff_10px,#d4ecff_10px,#d4ecff_20px)] p-8 shadow-sm"
      >
        <div className="space-y-2">
          <Label htmlFor="telephone">Téléphone du client :</Label>
          <Input
            id="telephone"
            placeholder="+221..."
            required
            value={telephone}
            onChange={(e) => setTelephone(e.target.value)}
          />
        </div>

        <Button
          type="submit"
          size="lg"
          disabled={searching}
          className="bg-brand-accent text-brand-accent-foreground hover:bg-brand-accent/90"
        >
          {searching ? "Recherche..." : "Rechercher la Proforma"}
        </Button>
      </form>

      {error && <FormMessage type="error">{error}</FormMessage>}
    </>
  );
}
