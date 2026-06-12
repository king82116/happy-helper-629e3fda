import { createFileRoute, Link } from "@tanstack/react-router";
import { useEffect, useState, useCallback } from "react";
import { supabase } from "@/integrations/supabase/client";

export const Route = createFileRoute("/_authenticated/operators/$operatorId")({
  component: OperatorDetailPage,
});

type Operator = {
  id: string;
  name: string;
  slug: string;
  status: string;
  wallet_endpoint: string | null;
  wallet_signing_secret: string | null;
  notes: string | null;
};
type ApiKey = {
  id: string;
  label: string;
  key_prefix: string;
  last_used_at: string | null;
  revoked_at: string | null;
  created_at: string;
};
type Ip = { id: string; ip_cidr: string; label: string | null };
type Domain = { id: string; domain: string; label: string | null };
type Game = { id: string; code: string; name: string; category: string };
type OperatorGame = {
  id: string;
  game_id: string;
  enabled: boolean;
  min_bet: number;
  max_bet: number;
  max_payout: number | null;
};

async function sha256Hex(text: string): Promise<string> {
  const buf = await crypto.subtle.digest("SHA-256", new TextEncoder().encode(text));
  return Array.from(new Uint8Array(buf))
    .map((b) => b.toString(16).padStart(2, "0"))
    .join("");
}

function randomKey(): string {
  const bytes = new Uint8Array(32);
  crypto.getRandomValues(bytes);
  return Array.from(bytes)
    .map((b) => b.toString(16).padStart(2, "0"))
    .join("");
}

function OperatorDetailPage() {
  const { operatorId } = Route.useParams();
  const [op, setOp] = useState<Operator | null>(null);
  const [keys, setKeys] = useState<ApiKey[]>([]);
  const [ips, setIps] = useState<Ip[]>([]);
  const [domains, setDomains] = useState<Domain[]>([]);
  const [games, setGames] = useState<Game[]>([]);
  const [opGames, setOpGames] = useState<OperatorGame[]>([]);
  const [newKeyLabel, setNewKeyLabel] = useState("");
  const [newKeyValue, setNewKeyValue] = useState<string | null>(null);
  const [newIp, setNewIp] = useState({ ip_cidr: "", label: "" });
  const [newDomain, setNewDomain] = useState({ domain: "", label: "" });
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    setLoading(true);
    const [opRes, keyRes, ipRes, domRes, gameRes, opGameRes] = await Promise.all([
      supabase.from("operators").select("*").eq("id", operatorId).maybeSingle(),
      supabase.from("api_keys").select("*").eq("operator_id", operatorId).order("created_at", { ascending: false }),
      supabase.from("ip_whitelist").select("*").eq("operator_id", operatorId).order("created_at"),
      supabase.from("domain_whitelist").select("*").eq("operator_id", operatorId).order("created_at"),
      supabase.from("games").select("*").order("name"),
      supabase.from("operator_games").select("*").eq("operator_id", operatorId),
    ]);
    setOp((opRes.data as Operator) ?? null);
    setKeys((keyRes.data ?? []) as ApiKey[]);
    setIps((ipRes.data ?? []) as Ip[]);
    setDomains((domRes.data ?? []) as Domain[]);
    setGames((gameRes.data ?? []) as Game[]);
    setOpGames((opGameRes.data ?? []) as OperatorGame[]);
    setLoading(false);
  }, [operatorId]);

  useEffect(() => {
    load();
  }, [load]);

  async function createKey(e: React.FormEvent) {
    e.preventDefault();
    if (!newKeyLabel.trim()) return;
    const raw = `sk_${randomKey()}`;
    const hash = await sha256Hex(raw);
    const prefix = raw.slice(0, 12);
    const { error } = await supabase.from("api_keys").insert({
      operator_id: operatorId,
      label: newKeyLabel,
      key_prefix: prefix,
      key_hash: hash,
    });
    if (error) {
      alert(error.message);
      return;
    }
    setNewKeyValue(raw);
    setNewKeyLabel("");
    load();
  }

  async function revokeKey(id: string) {
    await supabase.from("api_keys").update({ revoked_at: new Date().toISOString() }).eq("id", id);
    load();
  }
  async function deleteKey(id: string) {
    if (!confirm("Delete this key permanently?")) return;
    await supabase.from("api_keys").delete().eq("id", id);
    load();
  }

  async function addIp(e: React.FormEvent) {
    e.preventDefault();
    if (!newIp.ip_cidr.trim()) return;
    const { error } = await supabase.from("ip_whitelist").insert({
      operator_id: operatorId,
      ip_cidr: newIp.ip_cidr.trim(),
      label: newIp.label || null,
    });
    if (error) alert(error.message);
    setNewIp({ ip_cidr: "", label: "" });
    load();
  }
  async function removeIp(id: string) {
    await supabase.from("ip_whitelist").delete().eq("id", id);
    load();
  }

  async function addDomain(e: React.FormEvent) {
    e.preventDefault();
    if (!newDomain.domain.trim()) return;
    const { error } = await supabase.from("domain_whitelist").insert({
      operator_id: operatorId,
      domain: newDomain.domain.trim().toLowerCase(),
      label: newDomain.label || null,
    });
    if (error) alert(error.message);
    setNewDomain({ domain: "", label: "" });
    load();
  }
  async function removeDomain(id: string) {
    await supabase.from("domain_whitelist").delete().eq("id", id);
    load();
  }

  async function toggleGame(g: Game) {
    const existing = opGames.find((og) => og.game_id === g.id);
    if (existing) {
      await supabase.from("operator_games").update({ enabled: !existing.enabled }).eq("id", existing.id);
    } else {
      await supabase.from("operator_games").insert({ operator_id: operatorId, game_id: g.id, enabled: true });
    }
    load();
  }
  async function updateLimits(og: OperatorGame, patch: Partial<OperatorGame>) {
    await supabase.from("operator_games").update(patch).eq("id", og.id);
    load();
  }

  if (loading) return <div className="text-muted">Loading…</div>;
  if (!op) return <div className="text-muted">Operator not found. <Link to="/operators" className="underline">Back</Link></div>;

  return (
    <div className="space-y-8">
      <div className="flex items-center gap-3">
        <Link to="/operators" className="text-sm text-muted hover:text-fg">← Operators</Link>
        <h1 className="text-2xl font-semibold">{op.name}</h1>
        <span className="rounded-full bg-surface px-2 py-0.5 font-mono text-xs">{op.slug}</span>
      </div>

      {/* API Keys */}
      <section className="rounded-lg border border-border bg-surface p-5">
        <h2 className="mb-3 text-lg font-semibold">API Keys</h2>
        {newKeyValue && (
          <div className="mb-4 rounded-md border border-amber-500/40 bg-amber-500/10 p-3 text-sm">
            <p className="mb-1 font-medium text-amber-300">Copy this key now — it won't be shown again:</p>
            <code className="block break-all rounded bg-bg p-2 font-mono text-xs">{newKeyValue}</code>
            <button onClick={() => setNewKeyValue(null)} className="mt-2 text-xs text-muted underline">Dismiss</button>
          </div>
        )}
        <form onSubmit={createKey} className="mb-4 flex gap-2">
          <input
            placeholder="Key label (e.g. production)"
            value={newKeyLabel}
            onChange={(e) => setNewKeyLabel(e.target.value)}
            className="flex-1 rounded-md border border-border bg-bg px-3 py-2 text-sm"
          />
          <button className="rounded-md bg-primary px-3 py-2 text-sm text-primary-fg">Generate key</button>
        </form>
        <div className="overflow-hidden rounded border border-border">
          <table className="w-full text-sm">
            <thead className="bg-bg text-xs uppercase text-muted">
              <tr>
                <th className="px-3 py-2 text-left">Label</th>
                <th className="px-3 py-2 text-left">Prefix</th>
                <th className="px-3 py-2 text-left">Status</th>
                <th className="px-3 py-2 text-left">Last used</th>
                <th className="px-3 py-2"></th>
              </tr>
            </thead>
            <tbody>
              {keys.length === 0 && <tr><td colSpan={5} className="px-3 py-6 text-center text-muted">No keys yet.</td></tr>}
              {keys.map((k) => (
                <tr key={k.id} className="border-t border-border">
                  <td className="px-3 py-2">{k.label}</td>
                  <td className="px-3 py-2 font-mono text-xs">{k.key_prefix}…</td>
                  <td className="px-3 py-2">
                    {k.revoked_at ? <span className="text-red-400">revoked</span> : <span className="text-green-400">active</span>}
                  </td>
                  <td className="px-3 py-2 text-xs text-muted">{k.last_used_at ?? "never"}</td>
                  <td className="px-3 py-2 text-right">
                    {!k.revoked_at && (
                      <button onClick={() => revokeKey(k.id)} className="text-xs text-amber-400 hover:text-amber-300">Revoke</button>
                    )}
                    <button onClick={() => deleteKey(k.id)} className="ml-3 text-xs text-red-400 hover:text-red-300">Delete</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </section>

      {/* IP whitelist */}
      <section className="rounded-lg border border-border bg-surface p-5">
        <h2 className="mb-3 text-lg font-semibold">IP whitelist</h2>
        <form onSubmit={addIp} className="mb-4 flex gap-2">
          <input
            placeholder="1.2.3.4 or 10.0.0.0/24"
            value={newIp.ip_cidr}
            onChange={(e) => setNewIp({ ...newIp, ip_cidr: e.target.value })}
            className="flex-1 rounded-md border border-border bg-bg px-3 py-2 text-sm"
          />
          <input
            placeholder="Label (optional)"
            value={newIp.label}
            onChange={(e) => setNewIp({ ...newIp, label: e.target.value })}
            className="w-48 rounded-md border border-border bg-bg px-3 py-2 text-sm"
          />
          <button className="rounded-md bg-primary px-3 py-2 text-sm text-primary-fg">Add</button>
        </form>
        {ips.length === 0 ? (
          <p className="text-sm text-muted">No IPs — operator can call from anywhere.</p>
        ) : (
          <ul className="space-y-1 text-sm">
            {ips.map((ip) => (
              <li key={ip.id} className="flex items-center justify-between rounded border border-border px-3 py-1.5">
                <span><span className="font-mono">{ip.ip_cidr}</span>{ip.label && <span className="ml-2 text-muted">— {ip.label}</span>}</span>
                <button onClick={() => removeIp(ip.id)} className="text-xs text-red-400">Remove</button>
              </li>
            ))}
          </ul>
        )}
      </section>

      {/* Domain whitelist */}
      <section className="rounded-lg border border-border bg-surface p-5">
        <h2 className="mb-3 text-lg font-semibold">Domain whitelist</h2>
        <form onSubmit={addDomain} className="mb-4 flex gap-2">
          <input
            placeholder="casino.example.com"
            value={newDomain.domain}
            onChange={(e) => setNewDomain({ ...newDomain, domain: e.target.value })}
            className="flex-1 rounded-md border border-border bg-bg px-3 py-2 text-sm"
          />
          <input
            placeholder="Label (optional)"
            value={newDomain.label}
            onChange={(e) => setNewDomain({ ...newDomain, label: e.target.value })}
            className="w-48 rounded-md border border-border bg-bg px-3 py-2 text-sm"
          />
          <button className="rounded-md bg-primary px-3 py-2 text-sm text-primary-fg">Add</button>
        </form>
        {domains.length === 0 ? (
          <p className="text-sm text-muted">No domains — no Origin checks enforced.</p>
        ) : (
          <ul className="space-y-1 text-sm">
            {domains.map((d) => (
              <li key={d.id} className="flex items-center justify-between rounded border border-border px-3 py-1.5">
                <span><span className="font-mono">{d.domain}</span>{d.label && <span className="ml-2 text-muted">— {d.label}</span>}</span>
                <button onClick={() => removeDomain(d.id)} className="text-xs text-red-400">Remove</button>
              </li>
            ))}
          </ul>
        )}
      </section>

      {/* Per-operator games */}
      <section className="rounded-lg border border-border bg-surface p-5">
        <h2 className="mb-3 text-lg font-semibold">Enabled games & limits</h2>
        <div className="overflow-hidden rounded border border-border">
          <table className="w-full text-sm">
            <thead className="bg-bg text-xs uppercase text-muted">
              <tr>
                <th className="px-3 py-2 text-left">Game</th>
                <th className="px-3 py-2 text-left">Enabled</th>
                <th className="px-3 py-2 text-left">Min bet</th>
                <th className="px-3 py-2 text-left">Max bet</th>
                <th className="px-3 py-2 text-left">Max payout</th>
              </tr>
            </thead>
            <tbody>
              {games.map((g) => {
                const og = opGames.find((x) => x.game_id === g.id);
                return (
                  <tr key={g.id} className="border-t border-border">
                    <td className="px-3 py-2">
                      <div>{g.name}</div>
                      <div className="text-xs text-muted">{g.category}</div>
                    </td>
                    <td className="px-3 py-2">
                      <input type="checkbox" checked={og?.enabled ?? false} onChange={() => toggleGame(g)} />
                    </td>
                    <td className="px-3 py-2">
                      <input
                        type="number"
                        disabled={!og}
                        defaultValue={og?.min_bet ?? 1}
                        onBlur={(e) => og && updateLimits(og, { min_bet: Number(e.target.value) })}
                        className="w-24 rounded border border-border bg-bg px-2 py-1 text-xs"
                      />
                    </td>
                    <td className="px-3 py-2">
                      <input
                        type="number"
                        disabled={!og}
                        defaultValue={og?.max_bet ?? 100000}
                        onBlur={(e) => og && updateLimits(og, { max_bet: Number(e.target.value) })}
                        className="w-28 rounded border border-border bg-bg px-2 py-1 text-xs"
                      />
                    </td>
                    <td className="px-3 py-2">
                      <input
                        type="number"
                        disabled={!og}
                        defaultValue={og?.max_payout ?? ""}
                        placeholder="—"
                        onBlur={(e) => og && updateLimits(og, { max_payout: e.target.value ? Number(e.target.value) : null })}
                        className="w-28 rounded border border-border bg-bg px-2 py-1 text-xs"
                      />
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </section>

      {/* API endpoint hint */}
      <section className="rounded-lg border border-border bg-surface p-5 text-sm">
        <h2 className="mb-2 text-lg font-semibold">Integration</h2>
        <p className="mb-2 text-muted">Test an API key with:</p>
        <code className="block break-all rounded bg-bg p-2 font-mono text-xs">
          curl -H "Authorization: Bearer YOUR_KEY" {typeof window !== "undefined" ? window.location.origin : ""}/api/public/v1/ping
        </code>
      </section>
    </div>
  );
}
