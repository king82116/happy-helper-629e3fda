import { createFileRoute } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { supabase } from "@/integrations/supabase/client";

export const Route = createFileRoute("/_authenticated/dashboard")({
  component: Dashboard,
});

function Dashboard() {
  const [counts, setCounts] = useState<{ operators: number; games: number; apiKeys: number } | null>(null);

  useEffect(() => {
    (async () => {
      const [op, gm, ak] = await Promise.all([
        supabase.from("operators").select("*", { count: "exact", head: true }),
        supabase.from("games").select("*", { count: "exact", head: true }),
        supabase.from("api_keys").select("*", { count: "exact", head: true }),
      ]);
      setCounts({ operators: op.count ?? 0, games: gm.count ?? 0, apiKeys: ak.count ?? 0 });
    })();
  }, []);

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-semibold">Dashboard</h1>
      <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <Stat label="Operators" value={counts?.operators} />
        <Stat label="Games" value={counts?.games} />
        <Stat label="API keys" value={counts?.apiKeys} />
      </div>
    </div>
  );
}

function Stat({ label, value }: { label: string; value?: number }) {
  return (
    <div className="rounded-lg border border-border bg-surface p-4">
      <div className="text-xs uppercase tracking-wider text-muted">{label}</div>
      <div className="mt-1 text-3xl font-semibold">{value ?? "—"}</div>
    </div>
  );
}
