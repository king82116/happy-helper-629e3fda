import { createFileRoute } from "@tanstack/react-router";
import { authenticateSession, jsonResponse } from "@/lib/motorace-auth.server";

const ALLOWED_COLORS = new Set(["green", "violet", "red"]);
const ALLOWED_BIGSMALL = new Set(["big", "small"]);

export const Route = createFileRoute("/api/public/v1/motorace/bet")({
  server: {
    handlers: {
      POST: async ({ request }) => {
        const auth = await authenticateSession(request);
        if ("error" in auth) return auth.error;
        const { tokenHash } = auth;

        let body: { period_id?: string; bet_type?: string; bet_value?: string; amount?: number };
        try {
          body = await request.json();
        } catch {
          return jsonResponse({ error: "invalid_json" }, 400);
        }
        const periodId = String(body.period_id || "");
        const betType = String(body.bet_type || "");
        const betValue = String(body.bet_value || "").toLowerCase();
        const amount = Number(body.amount);

        if (!periodId) return jsonResponse({ error: "period_id_required" }, 400);
        if (!Number.isFinite(amount) || amount <= 0) return jsonResponse({ error: "invalid_amount" }, 400);

        if (betType === "number") {
          const n = Number(betValue);
          if (!Number.isInteger(n) || n < 1 || n > 10) return jsonResponse({ error: "invalid_number" }, 400);
        } else if (betType === "color") {
          if (!ALLOWED_COLORS.has(betValue)) return jsonResponse({ error: "invalid_color" }, 400);
        } else if (betType === "bigsmall") {
          if (!ALLOWED_BIGSMALL.has(betValue)) return jsonResponse({ error: "invalid_bigsmall" }, 400);
        } else {
          return jsonResponse({ error: "invalid_bet_type" }, 400);
        }

        const { supabaseAdmin } = await import("@/integrations/supabase/client.server");
        const { data, error } = await supabaseAdmin.rpc("place_motorace_bet", {
          _token_hash: tokenHash,
          _period_id: periodId,
          _bet_type: betType,
          _bet_value: betValue,
          _amount: amount,
        });
        if (error) {
          const msg = error.message || "bet_failed";
          const known = ["invalid_session", "insufficient_balance", "period_closed", "invalid_amount", "invalid_bet_type"];
          const matched = known.find((k) => msg.includes(k));
          return jsonResponse({ error: matched ?? "bet_failed", detail: msg }, 400);
        }
        const row = Array.isArray(data) ? data[0] : data;
        return jsonResponse({ bet_id: row?.bet_id, new_balance: Number(row?.new_balance) });
      },
    },
  },
});
