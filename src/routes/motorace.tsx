import { createFileRoute, useSearch } from "@tanstack/react-router";
import { useCallback, useEffect, useMemo, useState } from "react";

type StateResponse = {
  server_time: string;
  session: { display_name: string | null; balance: number; currency: string };
  current_period: { id: string; period_no: string; starts_at: string; ends_at: string; status: string };
  recent_results: Array<{ period_no: string; result_winner: number | null; settled_at: string }>;
  my_bets: Array<{
    id: string;
    period_id: string;
    bet_type: string;
    bet_value: string;
    amount: number;
    payout: number | null;
    status: string;
    created_at: string;
    motorace_periods: { period_no: string; result_winner: number | null };
  }>;
};

export const Route = createFileRoute("/motorace")({
  validateSearch: (s: Record<string, unknown>) => ({ token: (s.token as string) || "" }),
  component: MotoracePage,
  head: () => ({ meta: [{ title: "Moto Race" }] }),
});

const AMOUNT_CHIPS = [1, 10, 100, 1000];
const NUMBERS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
const GREEN_NUMS = new Set([1, 3, 7, 9]);
const RED_NUMS = new Set([2, 4, 6, 8]);

function numberColor(n: number) {
  if (n === 5) return "from-emerald-500 to-violet-500";
  if (n === 10) return "from-rose-500 to-violet-500";
  if (GREEN_NUMS.has(n)) return "from-emerald-600 to-emerald-500";
  return "from-rose-600 to-rose-500";
}

function MotoracePage() {
  const { token } = useSearch({ from: "/motorace" });
  const [state, setState] = useState<StateResponse | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [amount, setAmount] = useState<number>(10);
  const [now, setNow] = useState(Date.now());
  const [placing, setPlacing] = useState(false);
  const [toast, setToast] = useState<string | null>(null);

  const fetchState = useCallback(async () => {
    if (!token) return;
    try {
      const r = await fetch(`/api/public/v1/motorace/state?token=${token}`);
      if (!r.ok) {
        const j = await r.json().catch(() => ({}));
        setError(j.error || "load_failed");
        return;
      }
      const j: StateResponse = await r.json();
      setState(j);
      setError(null);
    } catch (e) {
      setError(String(e));
    }
  }, [token]);

  useEffect(() => { fetchState(); }, [fetchState]);
  useEffect(() => {
    const i = setInterval(() => setNow(Date.now()), 250);
    return () => clearInterval(i);
  }, []);
  useEffect(() => {
    const i = setInterval(fetchState, 3000);
    return () => clearInterval(i);
  }, [fetchState]);

  const endsMs = state ? new Date(state.current_period.ends_at).getTime() : 0;
  const secondsLeft = Math.max(0, Math.ceil((endsMs - now) / 1000));
  // refresh promptly when timer hits zero
  useEffect(() => {
    if (state && secondsLeft === 0) {
      const t = setTimeout(fetchState, 1500);
      return () => clearTimeout(t);
    }
  }, [secondsLeft, state, fetchState]);

  const placeBet = useCallback(
    async (betType: string, betValue: string) => {
      if (!state || placing) return;
      if (secondsLeft <= 3) { setToast("Round closing — wait for next"); return; }
      setPlacing(true);
      try {
        const r = await fetch("/api/public/v1/motorace/bet", {
          method: "POST",
          headers: { "content-type": "application/json", "x-session-token": token },
          body: JSON.stringify({
            period_id: state.current_period.id,
            bet_type: betType,
            bet_value: betValue,
            amount,
          }),
        });
        const j = await r.json();
        if (!r.ok) { setToast(j.error || "Bet failed"); }
        else { setToast(`Bet placed: ${betType} ${betValue} ₹${amount}`); fetchState(); }
      } finally {
        setPlacing(false);
        setTimeout(() => setToast(null), 2200);
      }
    },
    [state, placing, secondsLeft, token, amount, fetchState],
  );

  const totalSeconds = 60;
  const progress = Math.min(100, Math.max(0, (secondsLeft / totalSeconds) * 100));

  const balanceFmt = useMemo(() => {
    if (!state) return "—";
    return `${state.session.currency} ${state.session.balance.toLocaleString(undefined, { maximumFractionDigits: 2 })}`;
  }, [state]);

  if (!token) {
    return (
      <div className="min-h-screen bg-zinc-950 text-zinc-100 grid place-items-center p-6 text-center">
        <div className="space-y-4">
          <h1 className="text-3xl font-bold">Moto Race</h1>
          <p className="text-zinc-400 max-w-sm">Try the game with a free demo session (₹10,000 play balance).</p>
          <button
            onClick={async () => {
              const r = await fetch("/api/public/v1/motorace/demo");
              const j = await r.json();
              if (j.token) window.location.href = `/motorace?token=${j.token}`;
            }}
            className="inline-block rounded-xl bg-amber-500 px-6 py-3 font-bold text-zinc-950 hover:bg-amber-400"
          >
            ▶ Start Demo
          </button>
          <p className="text-xs text-zinc-500">Operators: launch via <code>/api/public/v1/motorace/launch</code></p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-b from-zinc-950 via-zinc-900 to-zinc-950 text-zinc-100">
      <div className="mx-auto max-w-md px-4 pb-24 pt-4 space-y-4">
        {/* header */}
        <header className="flex items-center justify-between rounded-2xl bg-zinc-900/80 border border-zinc-800 px-4 py-3">
          <div>
            <div className="text-xs uppercase tracking-wider text-zinc-400">Balance</div>
            <div className="text-xl font-bold">{balanceFmt}</div>
          </div>
          <div className="text-right">
            <div className="text-xs text-zinc-400">{state?.session.display_name || "Player"}</div>
            <button onClick={fetchState} className="mt-1 text-xs rounded-md bg-zinc-800 px-2 py-1 hover:bg-zinc-700">↻ Refresh</button>
          </div>
        </header>

        {/* period card */}
        <section className="rounded-2xl border border-amber-500/30 bg-gradient-to-br from-amber-950/40 to-zinc-900 p-4">
          <div className="flex items-end justify-between">
            <div>
              <div className="text-[10px] uppercase tracking-widest text-amber-300/80">Period</div>
              <div className="text-xl font-mono font-bold tracking-wider">{state?.current_period.period_no || "—"}</div>
            </div>
            <div className="text-right">
              <div className="text-[10px] uppercase tracking-widest text-zinc-400">Time left</div>
              <div className={`text-3xl font-bold tabular-nums ${secondsLeft <= 5 ? "text-rose-400" : "text-amber-300"}`}>
                {String(Math.floor(secondsLeft / 60)).padStart(2, "0")}:{String(secondsLeft % 60).padStart(2, "0")}
              </div>
            </div>
          </div>
          <div className="mt-3 h-2 rounded-full bg-zinc-800 overflow-hidden">
            <div className="h-full bg-gradient-to-r from-amber-500 to-rose-500 transition-[width] duration-300" style={{ width: `${progress}%` }} />
          </div>
        </section>

        {/* recent results */}
        <section className="rounded-2xl bg-zinc-900/70 border border-zinc-800 p-3">
          <div className="text-xs text-zinc-400 mb-2">Recent winners</div>
          <div className="flex gap-1.5 overflow-x-auto">
            {(state?.recent_results || []).map((r) => (
              <div
                key={r.period_no}
                title={r.period_no}
                className={`shrink-0 w-9 h-9 rounded-full grid place-items-center font-bold text-sm bg-gradient-to-br ${r.result_winner ? numberColor(r.result_winner) : "from-zinc-700 to-zinc-800"} shadow`}
              >
                {r.result_winner ?? "?"}
              </div>
            ))}
            {(!state?.recent_results || state.recent_results.length === 0) && (
              <div className="text-zinc-500 text-sm py-2">No rounds settled yet.</div>
            )}
          </div>
        </section>

        {/* amount chips */}
        <section className="rounded-2xl bg-zinc-900/70 border border-zinc-800 p-3">
          <div className="text-xs text-zinc-400 mb-2">Bet amount</div>
          <div className="flex gap-2">
            {AMOUNT_CHIPS.map((c) => (
              <button
                key={c}
                onClick={() => setAmount(c)}
                className={`flex-1 rounded-xl py-2 font-semibold border transition ${amount === c ? "bg-amber-500 text-zinc-950 border-amber-400" : "bg-zinc-800/60 border-zinc-700 hover:bg-zinc-800"}`}
              >
                ₹{c}
              </button>
            ))}
          </div>
        </section>

        {/* color bets */}
        <section className="grid grid-cols-3 gap-2">
          <button onClick={() => placeBet("color", "green")} disabled={placing} className="rounded-xl py-4 font-bold bg-gradient-to-br from-emerald-600 to-emerald-500 shadow-lg shadow-emerald-900/30 disabled:opacity-60">
            Green <span className="block text-[10px] font-normal opacity-80">2× (5: 1.5×)</span>
          </button>
          <button onClick={() => placeBet("color", "violet")} disabled={placing} className="rounded-xl py-4 font-bold bg-gradient-to-br from-violet-600 to-violet-500 shadow-lg shadow-violet-900/30 disabled:opacity-60">
            Violet <span className="block text-[10px] font-normal opacity-80">4.5×</span>
          </button>
          <button onClick={() => placeBet("color", "red")} disabled={placing} className="rounded-xl py-4 font-bold bg-gradient-to-br from-rose-600 to-rose-500 shadow-lg shadow-rose-900/30 disabled:opacity-60">
            Red <span className="block text-[10px] font-normal opacity-80">2× (10: 1.5×)</span>
          </button>
        </section>

        {/* number bets */}
        <section>
          <div className="text-xs text-zinc-400 mb-2 px-1">Pick a bike — 9×</div>
          <div className="grid grid-cols-5 gap-2">
            {NUMBERS.map((n) => (
              <button
                key={n}
                onClick={() => placeBet("number", String(n))}
                disabled={placing}
                className={`aspect-square rounded-xl font-extrabold text-xl bg-gradient-to-br ${numberColor(n)} shadow-md disabled:opacity-60`}
              >
                {n}
              </button>
            ))}
          </div>
        </section>

        {/* big / small */}
        <section className="grid grid-cols-2 gap-2">
          <button onClick={() => placeBet("bigsmall", "small")} disabled={placing} className="rounded-xl py-3 font-bold bg-zinc-800 border border-amber-500/30 disabled:opacity-60">
            Small (1–5) <span className="block text-[10px] opacity-70">2×</span>
          </button>
          <button onClick={() => placeBet("bigsmall", "big")} disabled={placing} className="rounded-xl py-3 font-bold bg-zinc-800 border border-amber-500/30 disabled:opacity-60">
            Big (6–10) <span className="block text-[10px] opacity-70">2×</span>
          </button>
        </section>

        {/* my bets */}
        <section className="rounded-2xl bg-zinc-900/70 border border-zinc-800 p-3">
          <div className="text-xs text-zinc-400 mb-2">My recent bets</div>
          <div className="space-y-1.5 max-h-72 overflow-y-auto">
            {(state?.my_bets || []).map((b) => (
              <div key={b.id} className="flex items-center justify-between text-sm bg-zinc-950/40 rounded-lg px-3 py-2">
                <div>
                  <div className="font-mono text-[11px] text-zinc-500">{b.motorace_periods.period_no}</div>
                  <div className="font-semibold capitalize">
                    {b.bet_type === "number" ? `#${b.bet_value}` : b.bet_value} <span className="text-zinc-500 font-normal">· ₹{b.amount}</span>
                  </div>
                </div>
                <div className={`text-sm font-bold ${b.status === "won" ? "text-emerald-400" : b.status === "lost" ? "text-rose-400" : "text-amber-300"}`}>
                  {b.status === "pending" ? "Pending" : b.status === "won" ? `+₹${b.payout}` : "Lost"}
                </div>
              </div>
            ))}
            {(!state?.my_bets || state.my_bets.length === 0) && (
              <div className="text-zinc-500 text-sm py-2">No bets yet.</div>
            )}
          </div>
        </section>

        {error && (
          <div className="fixed inset-x-0 top-4 mx-auto w-fit rounded-lg bg-rose-600 px-3 py-2 text-sm shadow-lg">{error}</div>
        )}
        {toast && (
          <div className="fixed inset-x-0 bottom-6 mx-auto w-fit rounded-lg bg-zinc-800 border border-zinc-700 px-4 py-2 text-sm shadow-lg">{toast}</div>
        )}
      </div>
    </div>
  );
}
