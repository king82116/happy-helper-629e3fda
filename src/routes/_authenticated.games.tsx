import { createFileRoute } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { supabase } from "@/integrations/supabase/client";

export const Route = createFileRoute("/_authenticated/games")({
  component: GamesPage,
});

type Game = { id: string; code: string; name: string; category: string; enabled: boolean };

function GamesPage() {
  const [games, setGames] = useState<Game[]>([]);

  async function load() {
    const { data } = await supabase.from("games").select("*").order("name");
    setGames((data ?? []) as Game[]);
  }

  useEffect(() => {
    load();
  }, []);

  async function toggle(g: Game) {
    await supabase.from("games").update({ enabled: !g.enabled }).eq("id", g.id);
    load();
  }

  return (
    <div className="space-y-6">
      <header>
        <h1 className="text-2xl font-semibold">Games catalog</h1>
        <p className="text-sm text-muted">Master list of supported games. Per-operator enablement comes from the operator page.</p>
      </header>
      <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        {games.map((g) => (
          <div key={g.id} className="rounded-lg border border-border bg-surface p-4">
            <div className="flex items-center justify-between">
              <div>
                <div className="font-medium">{g.name}</div>
                <div className="font-mono text-xs text-muted">{g.code} · {g.category}</div>
              </div>
              <button
                onClick={() => toggle(g)}
                className={`rounded-full px-2 py-0.5 text-xs ${
                  g.enabled ? "bg-green-500/20 text-green-300" : "bg-zinc-500/20 text-zinc-300"
                }`}
              >
                {g.enabled ? "Enabled" : "Disabled"}
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
