import { createFileRoute } from "@tanstack/react-router";
import { authenticateSession, jsonResponse } from "@/lib/motorace-auth.server";

const PERIOD_SECONDS = 60;

async function ensureOpenPeriod() {
  const { supabaseAdmin } = await import("@/integrations/supabase/client.server");
  const nowIso = new Date().toISOString();
  // Try existing open period
  const { data: open } = await supabaseAdmin
    .from("motorace_periods")
    .select("id, period_no, starts_at, ends_at, status")
    .eq("status", "open")
    .gt("ends_at", nowIso)
    .order("ends_at", { ascending: true })
    .limit(1)
    .maybeSingle();
  if (open) return open;
  // Create one
  const { data: noRow } = await supabaseAdmin.rpc("next_motorace_period_no");
  const periodNo = noRow as unknown as string;
  const startsAt = new Date();
  const endsAt = new Date(startsAt.getTime() + PERIOD_SECONDS * 1000);
  const { data: created, error } = await supabaseAdmin
    .from("motorace_periods")
    .insert({
      period_no: periodNo,
      starts_at: startsAt.toISOString(),
      ends_at: endsAt.toISOString(),
      status: "open",
    })
    .select("id, period_no, starts_at, ends_at, status")
    .single();
  if (error || !created) throw new Error("period_create_failed: " + error?.message);
  return created;
}

export const Route = createFileRoute("/api/public/v1/motorace/state")({
  server: {
    handlers: {
      GET: async ({ request }) => {
        const auth = await authenticateSession(request);
        if ("error" in auth) return auth.error;
        const { session } = auth;
        const { supabaseAdmin } = await import("@/integrations/supabase/client.server");

        const current = await ensureOpenPeriod();
        const { data: recent } = await supabaseAdmin
          .from("motorace_periods")
          .select("period_no, result_winner, settled_at")
          .eq("status", "settled")
          .order("settled_at", { ascending: false })
          .limit(20);

        const { data: myBets } = await supabaseAdmin
          .from("motorace_bets")
          .select("id, period_id, bet_type, bet_value, amount, payout, status, created_at, motorace_periods!inner(period_no, result_winner)")
          .eq("session_id", session.id)
          .order("created_at", { ascending: false })
          .limit(30);

        return jsonResponse({
          server_time: new Date().toISOString(),
          session: {
            display_name: session.display_name,
            balance: Number(session.balance),
            currency: session.currency,
          },
          current_period: current,
          recent_results: recent ?? [],
          my_bets: myBets ?? [],
        });
      },
    },
  },
});
