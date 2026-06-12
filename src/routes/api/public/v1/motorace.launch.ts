import { createFileRoute } from "@tanstack/react-router";
import { authenticateOperator, jsonResponse, newSessionToken, sha256 } from "@/lib/motorace-auth.server";

export const Route = createFileRoute("/api/public/v1/motorace/launch")({
  server: {
    handlers: {
      POST: async ({ request }) => {
        const auth = await authenticateOperator(request);
        if ("error" in auth) return auth.error;

        let body: {
          external_user_id?: string;
          display_name?: string;
          currency?: string;
          balance?: number;
          ttl_seconds?: number;
        };
        try {
          body = await request.json();
        } catch {
          return jsonResponse({ error: "invalid_json" }, 400);
        }

        const externalUserId = String(body.external_user_id || "").trim();
        if (!externalUserId) return jsonResponse({ error: "external_user_id_required" }, 400);
        const balance = Number(body.balance ?? 0);
        if (!Number.isFinite(balance) || balance < 0) return jsonResponse({ error: "invalid_balance" }, 400);
        const currency = (body.currency || "INR").toUpperCase().slice(0, 6);
        const ttl = Math.min(Math.max(Number(body.ttl_seconds || 7200), 300), 86400);
        const displayName = body.display_name?.slice(0, 60) || null;

        const token = newSessionToken();
        const tokenHash = sha256(token);
        const expiresAt = new Date(Date.now() + ttl * 1000).toISOString();

        const { supabaseAdmin } = await import("@/integrations/supabase/client.server");
        const { data, error } = await supabaseAdmin
          .from("player_sessions")
          .insert({
            operator_id: auth.operatorId,
            external_user_id: externalUserId,
            display_name: displayName,
            currency,
            balance,
            token_hash: tokenHash,
            expires_at: expiresAt,
          })
          .select("id")
          .single();
        if (error || !data) return jsonResponse({ error: "session_create_failed", detail: error?.message }, 500);

        const origin = new URL(request.url).origin;
        return jsonResponse({
          session_id: data.id,
          token,
          expires_at: expiresAt,
          launch_url: `${origin}/motorace?token=${token}`,
        });
      },
    },
  },
});
