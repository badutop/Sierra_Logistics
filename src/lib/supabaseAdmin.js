import "server-only";
import { createClient } from "@supabase/supabase-js";

let client;

// Bypasses RLS - only import this from route handlers / server components,
// never from a "use client" component or anything shipped to the browser.
// Lazily instantiated so importing this module never crashes Next.js's
// build-time route analysis if env vars aren't populated at that stage.
export function getSupabaseAdmin() {
  if (!client) {
    client = createClient(
      process.env.NEXT_PUBLIC_SUPABASE_URL,
      process.env.SUPABASE_SERVICE_ROLE_KEY,
      { auth: { persistSession: false } }
    );
  }
  return client;
}
