"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Image from "next/image";
import { supabase } from "@/lib/supabaseClient";
import { useAdminSession } from "@/lib/useAdminSession";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { FormMessage } from "@/components/form-message";

export default function AdminLoginPage() {
  const router = useRouter();
  const { isLoading, isAdmin } = useAdminSession();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState(null);
  const [submitting, setSubmitting] = useState(false);

  if (!isLoading && isAdmin) {
    router.replace("/admin");
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setError(null);
    setSubmitting(true);

    const { data, error: signInError } = await supabase.auth.signInWithPassword({
      email,
      password,
    });

    if (signInError) {
      setError("Identifiants incorrects.");
      setSubmitting(false);
      return;
    }

    const { data: adminRow } = await supabase
      .from("admins")
      .select("id")
      .eq("id", data.user.id)
      .maybeSingle();

    if (!adminRow) {
      setError("Ce compte n'est pas autorisé à accéder à l'espace d'administration.");
      await supabase.auth.signOut();
      setSubmitting(false);
      return;
    }

    router.push("/admin");
  }

  return (
    <div className="flex min-h-full items-center justify-center px-4 py-16">
      <div className="w-full max-w-sm rounded-2xl border-t-4 border-t-brand-accent bg-background p-8 shadow-md">
        <div className="mb-6 flex flex-col items-center text-center">
          <Image
            src="/images/sierra-logistics-logo-header.png"
            alt="Sierra Logistics"
            width={187}
            height={95}
            className="h-14 w-auto object-contain"
          />
          <h1 className="mt-4 text-xl font-bold text-primary">Espace Admin</h1>
          <p className="mt-1 text-sm text-muted-foreground">
            Réservé aux administrateurs et agents autorisés.
          </p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="email">Email</Label>
            <Input
              id="email"
              type="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="password">Mot de passe</Label>
            <Input
              id="password"
              type="password"
              required
              value={password}
              onChange={(e) => setPassword(e.target.value)}
            />
          </div>
          <Button
            type="submit"
            size="lg"
            disabled={submitting}
            className="w-full bg-brand-accent text-brand-accent-foreground hover:bg-brand-accent/90"
          >
            {submitting ? "Connexion..." : "Se connecter"}
          </Button>
        </form>

        {error && <FormMessage type="error">{error}</FormMessage>}
      </div>
    </div>
  );
}
