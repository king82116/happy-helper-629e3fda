import { createFileRoute } from "@tanstack/react-router";
import { randomInt } from "crypto";
import { authenticateSession, jsonResponse } from "@/lib/motorace-auth.server";

const PERIOD_SECONDS = 60;

function computePayout(betType: string, betValue: string, winner: number): number | null {
  if (betType === "number") return Number(betValue) === winner ? 9 : null;
  if (betType === "bigsmall") {
    if (betValue === "big" && winner >= 6) return 2;
    if (betValue === "small" && winner <= 5) return 2;
    return null;
  }
  if (betType === "color") {
    const greens = new Set([1, 3, 7, 9]);
    const reds = new Set([2, 4, 6, 8]);
    if (betValue === "violet") return winner === 5 || winner === 10 ? 4.5 : null;
    if (betValue === "green") return greens.has(winner) ? 2 : winner === 5 ? 1.5 : null;
    if (betValue === "red") return reds.has(winner) ? 2 : winner === 10 ? 1.5 : null;
  }
  return null;
}

async function settleDuePeriods() {
  const { supabaseAdmin } = await import("@/integrations/supabase/client.server");
  const nowIso = new Date().toISOString();
  const { data: due } = await supabaseAdmin
    .from("motorace_periods")
    .select("id, period_no")
    .eq("status", "open")
    .lte("ends_at", nowIso);

  for (const period of due ?? []) {
    const lockRes = await supabaseAdmin
      .from("motorace_periods")
      .update({ status: "locked" })
      .eq("id", period.id)
      .eq("status", "open")
      .select("id");
    if (!lockRes.data || lockRes.data.length === 0) continue;

    const winner = randomInt(1, 11);
    const { data: bets } = await supabaseAdmin
      .from("motorace_bets")
      .select("id, session_id, bet_type, bet_value, amount")
      .eq("period_id", period.id)
      .eq("status", "pending");

    for (const bet of bets ?? []) {
      const mult = computePayout(bet.bet_type, bet.bet_value, winner);
      const settledAt = new Date().toISOString();
      if (mult && mult > 0) {
        const payout = Number(bet.amount) * mult;
        await supabaseAdmin.from("motorace_bets").update({ status: "won", payout, settled_at: settledAt }).eq("id", bet.id);
        const { data: sess } = await supabaseAdmin.from("player_sessions").select("balance").eq("id", bet.session_id).maybeSingle();
        if (sess) {
          await supabaseAdmin.from("player_sessions").update({ balance: Number(sess.balance) + payout }).eq("id", bet.session_id);
        }
      } else {
        await supabaseAdmin.from("motorace_bets").update({ status: "lost", payout: 0, settled_at: settledAt }).eq("id", bet.id);
      }
    }

    await supabaseAdmin
      .from("motorace_periods")
      .update({ status: "settled", result_winner: winner, settled_at: new Date().toISOString() })
      .eq("id", period.id);
  }
}

async function ensureOpenPeriod() {
  const { supabaseAdmin } = await import("@/integrations/supabase/client.server");
  const nowIso = new Date().toISOString();
  const { data: open } = await supabaseAdmin
    .from("motorace_periods")
    .select("id, period_no, starts_at, ends_at, status")
    .eq("status", "open")
    .gt("ends_at", nowIso)
    .order("ends_at", { ascending: true })
    .limit(1)
    .maybeSingle();
  if (open) return open;
  const { data: noRow } = await supabaseAdmin.rpc("next_motorace_period_no");
  const periodNo = noRow as unknown as string;
  const startsAt = new Date();
  const endsAt = new Date(startsAt.getTime() + PERIOD_SECONDS * 1000);
  const { data: created, error } = await supabaseAdmin
    .from("motorace_periods")
    .insert({ period_no: periodNo, starts_at: startsAt.toISOString(), ends_at: endsAt.toISOString(), status: "open" })
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

        await settleDuePeriods();
        const current = await ensureOpenPeriod();

        // re-read balance after potential payouts
        const { data: freshSession } = await supabaseAdmin
          .from("player_sessions")
          .select("balance, currency, display_name")
          .eq("id", session.id)
          .maybeSingle();

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
            display_name: freshSession?.display_name ?? session.display_name,
            balance: Number(freshSession?.balance ?? session.balance),
            currency: freshSession?.currency ?? session.currency,
          },
          current_period: current,
          recent_results: recent ?? [],
          my_bets: myBets ?? [],
        });
      },
    },
  },
});
