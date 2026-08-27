"use client";

import { useEffect, useState } from "react";
import { supabase } from "@/lib/supabaseClient";

// Combine la session Supabase Auth avec le statut "admin" (présence dans la
// table `admins`, protégée par RLS). Tant que l'un des deux n'est pas résolu,
// isLoading reste vrai pour éviter un flash de contenu protégé.
export function useAdminSession() {
  const [session, setSession] = useState(undefined);
  const [admin, setAdmin] = useState(undefined);

  useEffect(() => {
    supabase.auth.getSession().then(({ data }) => setSession(data.session ?? null));

    const { data: listener } = supabase.auth.onAuthStateChange((_event, newSession) => {
      setSession(newSession);
    });

    return () => listener.subscription.unsubscribe();
  }, []);

  useEffect(() => {
    if (session === undefined) return;
    let cancelled = false;

    const query = session
      ? supabase.from("admins").select("*").eq("id", session.user.id).maybeSingle()
      : Promise.resolve({ data: null });

    query.then(({ data }) => {
      if (!cancelled) setAdmin(data ?? null);
    });

    return () => {
      cancelled = true;
    };
  }, [session]);

  const isLoading = session === undefined || admin === undefined;

  return {
    isLoading,
    session: session || null,
    admin: admin || null,
    isAdmin: Boolean(admin),
  };
}
