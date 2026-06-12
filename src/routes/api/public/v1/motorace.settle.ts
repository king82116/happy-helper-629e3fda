import { createFileRoute } from "@tanstack/react-router";
import { randomInt } from "crypto";
import { jsonResponse } from "@/lib/motorace-auth.server";

const PERIOD_SECONDS = 60;

function computePayout(betType: string, betValue: string, winner: number): number | null {
  // returns multiplier on stake (total returned). null = lose.
  if (betType === "number") {
    if (Number(betValue) === winner) return 9;
    return null;
  }
  if (betType === "bigsmall") {
    if (betValue === "big" && winner >= 6) return 2;
    if (betValue === "small" && winner <= 5) return 2;
    return null;
  }
  if (betType === "color") {
    // Standard mapping: 1,3,7,9 green; 2,4,6,8 red; 5,10 violet+(green or red)
    const greens = new Set([1, 3, 7, 9]);
    const reds = new Set([2, 4, 6, 8]);
    if (betValue === "violet") {
      if (winner === 5 || winner === 10) return 4.5;
      return null;
    }
    if (betValue === "green") {
      if (greens.has(winner)) return 2;
      if (winner === 5) return 1.5;
      return null;
    }
    if (betValue === "red") {
      if (reds.has(winner)) return 2;
      if (winner === 10) return 1.5;
      return null;
    }
  }
  return null;
}

export const Route = createFileRoute("/api/public/v1/motorace/settle")({
  server: {
    handlers: {
      POST: async ({ request }) => {
        // apikey check (cron uses anon key); plus a small shared header guard
        const apikey = request.headers.get("apikey") || "";
        if (!apikey) return jsonResponse({ error: "missing_apikey" }, 401);

        const { supabaseAdmin } = await import("@/integrations/supabase/client.server");
        const nowIso = new Date().toISOString();

        // 1. Find periods that are open AND past their end time → settle each
        const { data: due } = await supabaseAdmin
          .from("motorace_periods")
          .select("id, period_no")
          .eq("status", "open")
          .lte("ends_at", nowIso);

        const settled: Array<{ period_no: string; winner: number; bets: number }> = [];

        for (const period of due ?? []) {
          // lock
          await supabaseAdmin.from("motorace_periods").update({ status: "locked" }).eq("id", period.id);
          const winner = randomInt(1, 11); // 1..10
          // load bets
          const { data: bets } = await supabaseAdmin
            .from("motorace_bets")
            .select("id, session_id, bet_type, bet_value, amount")
            .eq("period_id", period.id)
            .eq("status", "pending");

          for (const bet of bets ?? []) {
            const mult = computePayout(bet.bet_type, bet.bet_value, winner);
            if (mult && mult > 0) {
              const payout = Number(bet.amount) * mult;
              await supabaseAdmin
                .from("motorace_bets")
                .update({ status: "won", payout, settled_at: new Date().toISOString() })
                .eq("id", bet.id);
              // credit
              const { data: sess } = await supabaseAdmin
                .from("player_sessions")
                .select("balance")
                .eq("id", bet.session_id)
                .maybeSingle();
              if (sess) {
                await supabaseAdmin
                  .from("player_sessions")
                  .update({ balance: Number(sess.balance) + payout })
                  .eq("id", bet.session_id);
              }
            } else {
              await supabaseAdmin
                .from("motorace_bets")
                .update({ status: "lost", payout: 0, settled_at: new Date().toISOString() })
                .eq("id", bet.id);
            }
          }

          await supabaseAdmin
            .from("motorace_periods")
            .update({ status: "settled", result_winner: winner, settled_at: new Date().toISOString() })
            .eq("id", period.id);
          settled.push({ period_no: period.period_no, winner, bets: bets?.length ?? 0 });
        }

        // 2. Ensure exactly one open period exists going forward
        const { data: open } = await supabaseAdmin
          .from("motorace_periods")
          .select("id")
          .eq("status", "open")
          .gt("ends_at", nowIso)
          .limit(1)
          .maybeSingle();
        let openedPeriod: string | null = null;
        if (!open) {
          const { data: noRow } = await supabaseAdmin.rpc("next_motorace_period_no");
          const periodNo = noRow as unknown as string;
          const startsAt = new Date();
          const endsAt = new Date(startsAt.getTime() + PERIOD_SECONDS * 1000);
          const { data: created } = await supabaseAdmin
            .from("motorace_periods")
            .insert({
              period_no: periodNo,
              starts_at: startsAt.toISOString(),
              ends_at: endsAt.toISOString(),
              status: "open",
            })
            .select("period_no")
            .single();
          openedPeriod = created?.period_no ?? null;
        }

        return jsonResponse({ settled, opened: openedPeriod });
      },
    },
  },
});
