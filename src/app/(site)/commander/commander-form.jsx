"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { lookupQuoteByTelephone } from "@/api/client";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { FormMessage } from "@/components/form-message";
import { RequiredMark } from "@/components/required-mark";

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
    try {
      const { id } = await lookupQuoteByTelephone(trimmed);
      router.push(`/facture-proforma?id=${id}`);
    } catch (err) {
      setError(err.status === 404 ? "Aucun devis trouvé pour ce numéro." : err.message);
    } finally {
      setSearching(false);
    }
  }

  return (
    <>
      <form
        onSubmit={handleSubmit}
        className="form-card"
      >
        <div className="space-y-2">
          <Label htmlFor="telephone">
            Téléphone du client <RequiredMark />
          </Label>
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
