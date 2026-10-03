"use client";

import { useState } from "react";
import { registerVehicle } from "@/api/client";
import { TRUCK_TYPES } from "@/lib/truckTypes";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { FormMessage } from "@/components/form-message";
import { RequiredMark } from "@/components/required-mark";

const STATUS_OPTIONS = [
  { value: "disponible", label: "Disponible" },
  { value: "maintenance", label: "En Maintenance" },
];

const initialState = {
  name: "",
  model: "",
  licensePlate: "",
  fuelType: "Diesel",
  status: "disponible",
  contactPhone: "",
  website: "",
};

export function InscriptionCamionForm() {
  const [form, setForm] = useState(initialState);
  const [status, setStatus] = useState(null);
  const [fieldErrors, setFieldErrors] = useState({});
  const [submitting, setSubmitting] = useState(false);

  const update = (field) => (value) => setForm((f) => ({ ...f, [field]: value }));

  async function handleSubmit(e) {
    e.preventDefault();
    setStatus(null);
    setFieldErrors({});
    setSubmitting(true);

    try {
      await registerVehicle({
        name: form.name,
        model: form.model,
        license_plate: form.licensePlate,
        fuel_type: form.fuelType,
        status: form.status,
        contact_phone: form.contactPhone,
        website: form.website,
      });

      setStatus({
        type: "success",
        message: "Votre inscription a été soumise avec succès ! Notre équipe vous contactera bientôt.",
      });
      setForm(initialState);
    } catch (err) {
      setStatus({ type: "error", message: err.message });
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
          <Label htmlFor="truck-name">
            Nom du Propriétaire <RequiredMark />
          </Label>
          <Input
            id="truck-name"
            required
            value={form.name}
            onChange={(e) => update("name")(e.target.value)}
          />
          {fieldErrors.name && <p className="text-sm text-destructive">{fieldErrors.name}</p>}
        </div>

        <div className="space-y-2">
          <Label htmlFor="truck-model">
            Modèle du Camion <RequiredMark />
          </Label>
          <Select value={form.model} onValueChange={update("model")} required>
            <SelectTrigger id="truck-model" className="w-full bg-background">
              <SelectValue placeholder="-- Sélectionnez un modèle --" />
            </SelectTrigger>
            <SelectContent>
              {TRUCK_TYPES.map((t) => (
                <SelectItem key={t} value={t}>
                  {t}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
          {fieldErrors.model && <p className="text-sm text-destructive">{fieldErrors.model}</p>}
        </div>

        <div className="space-y-2">
          <Label htmlFor="truck-plate">
            Numéro d&apos;Immatriculation <RequiredMark />
          </Label>
          <Input
            id="truck-plate"
            required
            value={form.licensePlate}
            onChange={(e) => update("licensePlate")(e.target.value)}
          />
          {fieldErrors.license_plate && <p className="text-sm text-destructive">{fieldErrors.license_plate}</p>}
        </div>

        <div className="space-y-2">
          <Label htmlFor="fuel-type">
            Type de Carburant <RequiredMark />
          </Label>
          <Select value={form.fuelType} onValueChange={update("fuelType")} required>
            <SelectTrigger id="fuel-type" className="w-full bg-background">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="Diesel">Diesel</SelectItem>
              <SelectItem value="Essence">Essence</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div className="space-y-2">
          <Label htmlFor="status">
            Statut <RequiredMark />
          </Label>
          <Select value={form.status} onValueChange={update("status")} required>
            <SelectTrigger id="status" className="w-full bg-background">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {STATUS_OPTIONS.map((s) => (
                <SelectItem key={s.value} value={s.value}>
                  {s.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>

        <div className="space-y-2">
          <Label htmlFor="contact-phone">
            N° de Téléphone du Transporteur <RequiredMark />
          </Label>
          <Input
            id="contact-phone"
            required
            value={form.contactPhone}
            onChange={(e) => update("contactPhone")(e.target.value)}
          />
          {fieldErrors.contact_phone && <p className="text-sm text-destructive">{fieldErrors.contact_phone}</p>}
        </div>

        <Button type="submit" size="lg" disabled={submitting} className="bg-brand-accent text-brand-accent-foreground hover:bg-brand-accent/90">
          {submitting ? "Envoi en cours..." : "Soumettre l'Inscription"}
        </Button>
      </form>

      {status && <FormMessage type={status.type}>{status.message}</FormMessage>}
    </>
  );
}
