import { createFileRoute } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { supabase } from "@/integrations/supabase/client";

export const Route = createFileRoute("/_authenticated/operators")({
  component: OperatorsPage,
});

type Operator = {
  id: string;
  name: string;
  slug: string;
  status: string;
  wallet_endpoint: string | null;
  created_at: string;
};

function OperatorsPage() {
  const [operators, setOperators] = useState<Operator[]>([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState({ name: "", slug: "", wallet_endpoint: "" });
  const [error, setError] = useState<string | null>(null);

  async function load() {
    setLoading(true);
    const { data, error: err } = await supabase
      .from("operators")
      .select("id,name,slug,status,wallet_endpoint,created_at")
      .order("created_at", { ascending: false });
    if (err) setError(err.message);
    setOperators((data ?? []) as Operator[]);
    setLoading(false);
  }

  useEffect(() => {
    load();
  }, []);

  async function onCreate(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    const { error: err } = await supabase.from("operators").insert({
      name: form.name,
      slug: form.slug.toLowerCase().replace(/[^a-z0-9-]/g, "-"),
      wallet_endpoint: form.wallet_endpoint || null,
    });
    if (err) {
      setError(err.message);
      return;
    }
    setForm({ name: "", slug: "", wallet_endpoint: "" });
    setShowForm(false);
    load();
  }

  async function toggleStatus(op: Operator) {
    const next = op.status === "active" ? "suspended" : "active";
    await supabase.from("operators").update({ status: next }).eq("id", op.id);
    load();
  }

  async function remove(op: Operator) {
    if (!confirm(`Delete operator "${op.name}"? This removes its API keys too.`)) return;
    await supabase.from("operators").delete().eq("id", op.id);
    load();
  }

  return (
    <div className="space-y-6">
      <header className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold">Operators</h1>
        <button
          onClick={() => setShowForm((s) => !s)}
          className="rounded-md bg-primary px-3 py-1.5 text-sm text-primary-fg"
        >
          {showForm ? "Cancel" : "+ New operator"}
        </button>
      </header>

      {showForm && (
        <form onSubmit={onCreate} className="grid grid-cols-1 gap-3 rounded-lg border border-border bg-surface p-4 sm:grid-cols-3">
          <input
            required
            placeholder="Name (e.g. Acme Casino)"
            value={form.name}
            onChange={(e) => setForm({ ...form, name: e.target.value })}
            className="rounded-md border border-border bg-bg px-3 py-2 text-sm"
          />
          <input
            required
            placeholder="slug (acme-casino)"
            value={form.slug}
            onChange={(e) => setForm({ ...form, slug: e.target.value })}
            className="rounded-md border border-border bg-bg px-3 py-2 text-sm"
          />
          <input
            placeholder="wallet endpoint URL (optional)"
            value={form.wallet_endpoint}
            onChange={(e) => setForm({ ...form, wallet_endpoint: e.target.value })}
            className="rounded-md border border-border bg-bg px-3 py-2 text-sm"
          />
          <div className="sm:col-span-3 flex items-center justify-between">
            {error && <p className="text-sm text-red-500">{error}</p>}
            <button type="submit" className="ml-auto rounded-md bg-primary px-3 py-1.5 text-sm text-primary-fg">
              Create
            </button>
          </div>
        </form>
      )}

      <div className="overflow-hidden rounded-lg border border-border">
        <table className="w-full text-sm">
          <thead className="bg-surface text-xs uppercase tracking-wider text-muted">
            <tr>
              <th className="px-3 py-2 text-left">Name</th>
              <th className="px-3 py-2 text-left">Slug</th>
              <th className="px-3 py-2 text-left">Status</th>
              <th className="px-3 py-2 text-left">Wallet endpoint</th>
              <th className="px-3 py-2"></th>
            </tr>
          </thead>
          <tbody>
            {loading && (
              <tr>
                <td colSpan={5} className="px-3 py-8 text-center text-muted">
                  Loading…
                </td>
              </tr>
            )}
            {!loading && operators.length === 0 && (
              <tr>
                <td colSpan={5} className="px-3 py-8 text-center text-muted">
                  No operators yet. Create your first one.
                </td>
              </tr>
            )}
            {operators.map((op) => (
              <tr key={op.id} className="border-t border-border">
                <td className="px-3 py-2 font-medium">{op.name}</td>
                <td className="px-3 py-2 font-mono text-xs">{op.slug}</td>
                <td className="px-3 py-2">
                  <span
                    className={`rounded-full px-2 py-0.5 text-xs ${
                      op.status === "active" ? "bg-green-500/20 text-green-300" : "bg-amber-500/20 text-amber-300"
                    }`}
                  >
                    {op.status}
                  </span>
                </td>
                <td className="px-3 py-2 truncate text-xs text-muted">{op.wallet_endpoint ?? "—"}</td>
                <td className="px-3 py-2 text-right">
                  <button onClick={() => toggleStatus(op)} className="text-xs text-muted hover:text-fg">
                    {op.status === "active" ? "Suspend" : "Activate"}
                  </button>
                  <button onClick={() => remove(op)} className="ml-3 text-xs text-red-400 hover:text-red-300">
                    Delete
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
