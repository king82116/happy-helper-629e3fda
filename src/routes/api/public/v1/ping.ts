import { createFileRoute } from "@tanstack/react-router";
import { createHash } from "crypto";

function jsonResponse(body: unknown, status = 200) {
  return new Response(JSON.stringify(body), {
    status,
    headers: { "content-type": "application/json" },
  });
}

async function authenticate(request: Request) {
  const auth = request.headers.get("authorization") ?? "";
  const match = auth.match(/^Bearer\s+(.+)$/i);
  if (!match) return { error: jsonResponse({ error: "missing_authorization" }, 401) };
  const raw = match[1].trim();
  const hash = createHash("sha256").update(raw).digest("hex");

  const { supabaseAdmin } = await import("@/integrations/supabase/client.server");
  const { data: key } = await supabaseAdmin
    .from("api_keys")
    .select("id, operator_id, revoked_at, operators!inner(id, name, slug, status)")
    .eq("key_hash", hash)
    .maybeSingle();

  if (!key) return { error: jsonResponse({ error: "invalid_key" }, 401) };
  if (key.revoked_at) return { error: jsonResponse({ error: "key_revoked" }, 401) };
  const operator = (key as unknown as { operators: { id: string; name: string; slug: string; status: string } }).operators;
  if (!operator || operator.status !== "active") {
    return { error: jsonResponse({ error: "operator_inactive" }, 403) };
  }

  // IP whitelist check
  const ip =
    request.headers.get("cf-connecting-ip") ||
    request.headers.get("x-forwarded-for")?.split(",")[0].trim() ||
    "";
  const { data: ips } = await supabaseAdmin
    .from("ip_whitelist")
    .select("ip_cidr")
    .eq("operator_id", operator.id);
  if (ips && ips.length > 0 && ip) {
    const allowed = ips.some((row) => row.ip_cidr === ip);
    if (!allowed) return { error: jsonResponse({ error: "ip_not_allowed", ip }, 403) };
  }

  // touch last_used_at (fire and forget)
  void supabaseAdmin.from("api_keys").update({ last_used_at: new Date().toISOString() }).eq("id", key.id);

  return { operator };
}

export const Route = createFileRoute("/api/public/v1/ping")({
  server: {
    handlers: {
      GET: async ({ request }) => {
        const result = await authenticate(request);
        if ("error" in result) return result.error;
        return jsonResponse({ ok: true, operator: result.operator });
      },
    },
  },
});
