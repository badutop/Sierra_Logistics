"use client";

import { useState } from "react";
import { supabase } from "@/lib/supabaseClient";
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
};

export function InscriptionCamionForm() {
  const [form, setForm] = useState(initialState);
  const [status, setStatus] = useState(null);

  const update = (field) => (value) => setForm((f) => ({ ...f, [field]: value }));

  async function handleSubmit(e) {
    e.preventDefault();
    setStatus(null);

    const { error } = await supabase.from("vehicles").insert([
      {
        name: form.name,
        model: form.model,
        license_plate: form.licensePlate,
        fuel_type: form.fuelType,
        status: form.status,
        contact_phone: form.contactPhone,
      },
    ]);

    if (error) {
      setStatus({ type: "error", message: error.message });
    } else {
      setStatus({
        type: "success",
        message: "Votre inscription a été soumise avec succès ! Notre équipe vous contactera bientôt.",
      });
      setForm(initialState);
    }
  }

  return (
    <>
      <form
        onSubmit={handleSubmit}
        className="form-card"
      >
        <div className="space-y-2">
          <Label htmlFor="truck-name">Nom du Propriétaire :</Label>
          <Input
            id="truck-name"
            required
            value={form.name}
            onChange={(e) => update("name")(e.target.value)}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="truck-model">Modèle du Camion :</Label>
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
        </div>

        <div className="space-y-2">
          <Label htmlFor="truck-plate">Numéro d&apos;Immatriculation :</Label>
          <Input
            id="truck-plate"
            required
            value={form.licensePlate}
            onChange={(e) => update("licensePlate")(e.target.value)}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="fuel-type">Type de Carburant :</Label>
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
          <Label htmlFor="status">Statut :</Label>
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
          <Label htmlFor="contact-phone">N° de Téléphone du Transporteur :</Label>
          <Input
            id="contact-phone"
            required
            value={form.contactPhone}
            onChange={(e) => update("contactPhone")(e.target.value)}
          />
        </div>

        <Button type="submit" size="lg" className="bg-brand-accent text-brand-accent-foreground hover:bg-brand-accent/90">
          Soumettre l&apos;Inscription
        </Button>
      </form>

      {status && <FormMessage type={status.type}>{status.message}</FormMessage>}
    </>
  );
}
