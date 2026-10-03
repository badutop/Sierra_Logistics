"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { createQuote } from "@/api/client";
import { VILLES_SENEGAL } from "@/lib/pricing";
import { TRUCK_TYPES } from "@/lib/truckTypes";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { FormMessage } from "@/components/form-message";
import { RequiredMark } from "@/components/required-mark";

const TYPES_MARCHANDISE = [
  "Produits agricoles",
  "Matériaux de construction",
  "Équipements industriels",
  "Marchandises générales",
  "Produits réfrigérés",
  "Autres",
];

const initialState = {
  nom: "",
  email: "",
  telephone: "",
  villeDepart: "",
  villeArrivee: "",
  typeMarchandise: "",
  poids: "",
  typeCamion: "",
  dateExpedition: "",
  infosAdditionnelles: "",
  website: "",
};

export function DevisForm() {
  const router = useRouter();
  const [form, setForm] = useState(initialState);
  const [error, setError] = useState(null);
  const [fieldErrors, setFieldErrors] = useState({});
  const [submitting, setSubmitting] = useState(false);

  const update = (field) => (value) => setForm((f) => ({ ...f, [field]: value }));

  async function handleSubmit(e) {
    e.preventDefault();
    setError(null);
    setFieldErrors({});

    if (!form.villeDepart || !form.villeArrivee || !form.typeCamion || !form.poids) {
      setError("Veuillez compléter tous les champs requis.");
      return;
    }

    setSubmitting(true);
    try {
      const quote = await createQuote({
        nom: form.nom,
        email: form.email,
        telephone: form.telephone,
        ville_depart: form.villeDepart,
        ville_arrivee: form.villeArrivee,
        type_marchandise: form.typeMarchandise,
        poids: Number(form.poids) || 0,
        type_vehicle: form.typeCamion,
        date_expedition: form.dateExpedition,
        infos_additionnelles: form.infosAdditionnelles,
        website: form.website,
      });

      router.push(`/facture-proforma?id=${quote.id}`);
    } catch (err) {
      setError(err.message);
      if (err.fieldErrors) setFieldErrors(err.fieldErrors);
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <>
      <form
        onSubmit={handleSubmit}
        className="form-card"
      >
        <p className="text-sm text-muted-foreground">
          <RequiredMark /> Champs obligatoires
        </p>

        <input
          type="text"
          name="website"
          value={form.website}
          onChange={(e) => update("website")(e.target.value)}
          tabIndex={-1}
          autoComplete="off"
          aria-hidden="true"
          className="hidden"
        />

        <div className="space-y-2">
          <Label htmlFor="nom">
            Nom Complet <RequiredMark />
          </Label>
          <Input id="nom" required value={form.nom} onChange={(e) => update("nom")(e.target.value)} />
          {fieldErrors.nom && <p className="text-sm text-destructive">{fieldErrors.nom}</p>}
        </div>

        <div className="space-y-2">
          <Label htmlFor="email">
            Adresse Email <RequiredMark />
          </Label>
          <Input
            id="email"
            type="email"
            required
            value={form.email}
            onChange={(e) => update("email")(e.target.value)}
          />
          {fieldErrors.email && <p className="text-sm text-destructive">{fieldErrors.email}</p>}
        </div>

        <div className="space-y-2">
          <Label htmlFor="telephone">
            Téléphone <RequiredMark />
          </Label>
          <Input
            id="telephone"
            type="tel"
            required
            value={form.telephone}
            onChange={(e) => update("telephone")(e.target.value)}
          />
          {fieldErrors.telephone && <p className="text-sm text-destructive">{fieldErrors.telephone}</p>}
        </div>

        <div className="space-y-2">
          <Label htmlFor="ville-depart">
            Ville de Départ <RequiredMark />
          </Label>
          <Select value={form.villeDepart} onValueChange={update("villeDepart")} required>
            <SelectTrigger id="ville-depart" className="w-full bg-background">
              <SelectValue placeholder="-- Sélectionner une ville --" />
            </SelectTrigger>
            <SelectContent>
              {VILLES_SENEGAL.map((v) => (
                <SelectItem key={v.name} value={v.name}>
                  {v.name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
          {fieldErrors.ville_depart && <p className="text-sm text-destructive">{fieldErrors.ville_depart}</p>}
        </div>

        <div className="space-y-2">
          <Label htmlFor="ville-arrivee">
            Ville d&apos;Arrivée <RequiredMark />
          </Label>
          <Select value={form.villeArrivee} onValueChange={update("villeArrivee")} required>
            <SelectTrigger id="ville-arrivee" className="w-full bg-background">
              <SelectValue placeholder="-- Sélectionner une ville --" />
            </SelectTrigger>
            <SelectContent>
              {VILLES_SENEGAL.map((v) => (
                <SelectItem key={v.name} value={v.name}>
                  {v.name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
          {fieldErrors.ville_arrivee && <p className="text-sm text-destructive">{fieldErrors.ville_arrivee}</p>}
        </div>

        <div className="space-y-2">
          <Label htmlFor="type-marchandise">
            Type de Marchandise <RequiredMark />
          </Label>
          <Select value={form.typeMarchandise} onValueChange={update("typeMarchandise")} required>
            <SelectTrigger id="type-marchandise" className="w-full bg-background">
              <SelectValue placeholder="-- Sélectionnez --" />
            </SelectTrigger>
            <SelectContent>
              {TYPES_MARCHANDISE.map((t) => (
                <SelectItem key={t} value={t}>
                  {t}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
          {fieldErrors.type_marchandise && <p className="text-sm text-destructive">{fieldErrors.type_marchandise}</p>}
        </div>

        <div className="space-y-2">
          <Label htmlFor="poids">
            Poids estimé (kg) <RequiredMark />
          </Label>
          <Input
            id="poids"
            type="number"
            required
            placeholder="Ex: 150"
            value={form.poids}
            onChange={(e) => update("poids")(e.target.value)}
          />
          {fieldErrors.poids && <p className="text-sm text-destructive">{fieldErrors.poids}</p>}
        </div>

        <div className="space-y-2">
          <Label htmlFor="type-camion">
            Type de Camion <RequiredMark />
          </Label>
          <Select value={form.typeCamion} onValueChange={update("typeCamion")} required>
            <SelectTrigger id="type-camion" className="w-full bg-background">
              <SelectValue placeholder="-- Sélectionnez --" />
            </SelectTrigger>
            <SelectContent>
              {TRUCK_TYPES.map((t) => (
                <SelectItem key={t} value={t}>
                  {t}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
          {fieldErrors.type_vehicle && <p className="text-sm text-destructive">{fieldErrors.type_vehicle}</p>}
        </div>

        <div className="space-y-2">
          <Label htmlFor="date-expedition">
            Date d&apos;Expédition <RequiredMark />
          </Label>
          <Input
            id="date-expedition"
            type="date"
            required
            value={form.dateExpedition}
            onChange={(e) => update("dateExpedition")(e.target.value)}
          />
          {fieldErrors.date_expedition && <p className="text-sm text-destructive">{fieldErrors.date_expedition}</p>}
        </div>

        <div className="space-y-2">
          <Label htmlFor="infos-additionnelles">Informations complémentaires :</Label>
          <Textarea
            id="infos-additionnelles"
            rows={4}
            placeholder="Ajoutez toute information utile concernant votre expédition (ex : conditions particulières, horaires, contact à l'arrivée...)"
            value={form.infosAdditionnelles}
            onChange={(e) => update("infosAdditionnelles")(e.target.value)}
          />
        </div>

        <Button
          type="submit"
          size="lg"
          disabled={submitting}
          className="bg-brand-accent text-brand-accent-foreground hover:bg-brand-accent/90"
        >
          {submitting ? "Envoi en cours..." : "Soumettre la Demande"}
        </Button>
      </form>

      {error && <FormMessage type="error">Erreur : {error}</FormMessage>}
    </>
  );
}
