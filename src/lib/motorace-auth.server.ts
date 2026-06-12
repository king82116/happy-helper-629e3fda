import { createHash, randomBytes } from "crypto";

export function jsonResponse(body: unknown, status = 200) {
  return new Response(JSON.stringify(body), {
    status,
    headers: { "content-type": "application/json", "cache-control": "no-store" },
  });
}

export function sha256(input: string) {
  return createHash("sha256").update(input).digest("hex");
}

export function newSessionToken() {
  return randomBytes(32).toString("hex");
}

export async function authenticateOperator(request: Request) {
  const auth = request.headers.get("authorization") ?? "";
  const match = auth.match(/^Bearer\s+(.+)$/i);
  if (!match) return { error: jsonResponse({ error: "missing_authorization" }, 401) };
  const hash = sha256(match[1].trim());

  const { supabaseAdmin } = await import("@/integrations/supabase/client.server");
  const { data: key } = await supabaseAdmin
    .from("api_keys")
    .select("id, operator_id, revoked_at, operators!inner(id, name, status)")
    .eq("key_hash", hash)
    .maybeSingle();

  if (!key) return { error: jsonResponse({ error: "invalid_key" }, 401) };
  if (key.revoked_at) return { error: jsonResponse({ error: "key_revoked" }, 401) };
  const operator = (key as unknown as { operators: { id: string; name: string; status: string } }).operators;
  if (!operator || operator.status !== "active") {
    return { error: jsonResponse({ error: "operator_inactive" }, 403) };
  }
  void supabaseAdmin.from("api_keys").update({ last_used_at: new Date().toISOString() }).eq("id", key.id);
  return { operatorId: operator.id, operatorName: operator.name };
}

export async function authenticateSession(request: Request) {
  const url = new URL(request.url);
  const token =
    request.headers.get("x-session-token") ||
    url.searchParams.get("token") ||
    "";
  if (!token) return { error: jsonResponse({ error: "missing_session_token" }, 401) };
  const tokenHash = sha256(token);
  const { supabaseAdmin } = await import("@/integrations/supabase/client.server");
  const { data: session } = await supabaseAdmin
    .from("player_sessions")
    .select("id, balance, currency, display_name, expires_at, operator_id")
    .eq("token_hash", tokenHash)
    .maybeSingle();
  if (!session) return { error: jsonResponse({ error: "invalid_session" }, 401) };
  if (new Date(session.expires_at) <= new Date()) {
    return { error: jsonResponse({ error: "session_expired" }, 401) };
  }
  return { session, tokenHash };
}
