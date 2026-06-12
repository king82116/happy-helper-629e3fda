import { createFileRoute } from "@tanstack/react-router";
import { newSessionToken, sha256 } from "@/lib/motorace-auth.server";

export const Route = createFileRoute("/api/public/v1/motorace/demo")({
  server: {
    handlers: {
      GET: async ({ request }) => {
        const { supabaseAdmin } = await import("@/integrations/supabase/client.server");

        // get-or-create demo operator
        let { data: op } = await supabaseAdmin
          .from("operators")
          .select("id")
          .eq("slug", "demo")
          .maybeSingle();
        if (!op) {
          const ins = await supabaseAdmin
            .from("operators")
            .insert({ name: "Demo", slug: "demo", status: "active" })
            .select("id")
            .single();
          op = ins.data;
        }
        if (!op) return new Response("operator_init_failed", { status: 500 });

        const token = newSessionToken();
        const tokenHash = sha256(token);
        const expiresAt = new Date(Date.now() + 2 * 60 * 60 * 1000).toISOString();

        const { error } = await supabaseAdmin.from("player_sessions").insert({
          operator_id: op.id,
          external_user_id: `demo-${Date.now()}`,
          display_name: "Demo Player",
          currency: "INR",
          balance: 10000,
          token_hash: tokenHash,
          expires_at: expiresAt,
        });
        if (error) return new Response(`session_failed: ${error.message}`, { status: 500 });

        const origin = new URL(request.url).origin;
        return Response.redirect(`${origin}/motorace?token=${token}`, 302);
      },
    },
  },
});
