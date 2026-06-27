import { createFileRoute, useSearch } from "@tanstack/react-router";
import { useEffect, useMemo, useRef, useState } from "react";

export const Route = createFileRoute("/motorace")({
  validateSearch: (s: Record<string, unknown>) => ({ token: (s.token as string) || "" }),
  component: MotoracePage,
  head: () => ({ meta: [{ title: "MotoRace 1Min" }] }),
});

type StateResp = {
  server_time: string;
  session: { display_name: string; balance: number; currency: string };
  current_period: { id: string; period_no: string; starts_at: string; ends_at: string; status: string };
  recent_results: { period_no: string; result_winner: number | null; settled_at: string }[];
  my_bets: {
    id: string;
    period_id: string;
    bet_type: string;
    bet_value: string;
    amount: number;
    payout: number | null;
    status: string;
    created_at: string;
    motorace_periods: { period_no: string; result_winner: number | null };
  }[];
};

const BIKE_COLORS = [
  "#22c55e", "#ef4444", "#22c55e", "#ef4444", "#a855f7",
  "#ef4444", "#22c55e", "#ef4444", "#22c55e", "#a855f7",
];
const NUM_COLOR_LABEL = (n: number): ("red" | "green" | "violet")[] => {
  const greens = new Set([1, 3, 7, 9]);
  const reds = new Set([2, 4, 6, 8]);
  if (n === 5 || n === 10) return ["violet", n === 5 ? "green" : "red"];
  return [greens.has(n) ? "green" : "red"];
};

function MotoracePage() {
  const { token } = useSearch({ from: "/motorace" });
  const [tokenState, setTokenState] = useState(token);
  const [state, setState] = useState<StateResp | null>(null);
  const [tab, setTab] = useState<"number" | "color" | "bigsmall">("number");
  const [amount, setAmount] = useState(10);
  const [msg, setMsg] = useState<{ kind: "err" | "ok"; text: string } | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const [now, setNow] = useState(() => Date.now());
  const [historyTab, setHistoryTab] = useState<"results" | "mine">("results");
  const lastPeriod = useRef<string | null>(null);

  // bootstrap demo token if none
  useEffect(() => {
    if (tokenState) return;
    fetch("/api/public/v1/motorace/demo")
      .then((r) => r.json())
      .then((j) => {
        if (j.token) {
          setTokenState(j.token);
          const url = new URL(window.location.href);
          url.searchParams.set("token", j.token);
          window.history.replaceState({}, "", url.toString());
        }
      })
      .catch(() => setMsg({ kind: "err", text: "Demo could not start" }));
  }, [tokenState]);

  // poll state
  useEffect(() => {
    if (!tokenState) return;
    let alive = true;
    const fetchState = async () => {
      try {
        const r = await fetch("/api/public/v1/motorace/state", { headers: { Authorization: `Bearer ${tokenState}` } });
        const j = (await r.json()) as StateResp;
        if (!alive) return;
        setState(j);
        if (lastPeriod.current && lastPeriod.current !== j.current_period.id) {
          // period flipped — surface latest result
          const latest = j.recent_results[0];
          if (latest?.result_winner != null) {
            setMsg({ kind: "ok", text: `Period ${latest.period_no} — Winner #${latest.result_winner}` });
            setTimeout(() => setMsg(null), 4000);
          }
        }
        lastPeriod.current = j.current_period.id;
      } catch {}
    };
    fetchState();
    const id = setInterval(fetchState, 2500);
    const tick = setInterval(() => setNow(Date.now()), 250);
    return () => { alive = false; clearInterval(id); clearInterval(tick); };
  }, [tokenState]);

  const endsMs = state ? new Date(state.current_period.ends_at).getTime() : 0;
  const remaining = Math.max(0, Math.ceil((endsMs - now) / 1000));
  const mm = Math.floor(remaining / 60).toString().padStart(2, "0");
  const ss = (remaining % 60).toString().padStart(2, "0");
  const closed = remaining <= 3;

  const placeBet = async (betType: string, betValue: string) => {
    if (!tokenState || !state || submitting) return;
    if (closed) { setMsg({ kind: "err", text: "Betting closed for this period" }); return; }
    if (amount <= 0) { setMsg({ kind: "err", text: "Enter amount" }); return; }
    if (amount > state.session.balance) { setMsg({ kind: "err", text: "Insufficient balance" }); return; }
    setSubmitting(true);
    try {
      const r = await fetch("/api/public/v1/motorace/bet", {
        method: "POST",
        headers: { "Content-Type": "application/json", Authorization: `Bearer ${tokenState}` },
        body: JSON.stringify({ period_id: state.current_period.id, bet_type: betType, bet_value: betValue, amount }),
      });
      const j = await r.json();
      if (!r.ok) throw new Error(j.error || "bet_failed");
      setMsg({ kind: "ok", text: `Bet placed: ${betType}/${betValue} ₹${amount}` });
      setTimeout(() => setMsg(null), 2500);
      setState((s) => (s ? { ...s, session: { ...s.session, balance: j.balance } } : s));
    } catch (e: unknown) {
      const message = e instanceof Error ? e.message : String(e);
      setMsg({ kind: "err", text: message });
    } finally {
      setSubmitting(false);
    }
  };

  // race animation: when remaining <= 20, bikes accelerate
  const racing = remaining > 0 && remaining <= 20;
  const progress = useMemo(() => {
    return Array.from({ length: 10 }, (_, i) => {
      if (!racing) return 8;
      const base = 8 + ((20 - remaining) / 20) * 70;
      const wobble = Math.sin((now / 250) + i) * 4;
      return Math.min(80, base + wobble + ((i * 7) % 9));
    });
  }, [racing, remaining, now]);

  return (
    <main className="min-h-[100dvh] w-full text-white" style={{ background: "#0b0f1a" }}>
      <div className="mx-auto" style={{ maxWidth: 480 }}>
        {/* Header */}
        <header className="flex items-center justify-between px-4 py-3" style={{ background: "linear-gradient(180deg,#1b2238,#0b0f1a)" }}>
          <div>
            <div className="text-xs opacity-70">MotoRace 1Min</div>
            <div className="text-lg font-bold">{state?.current_period.period_no ?? "—"}</div>
          </div>
          <div className="text-right">
            <div className="text-xs opacity-70">Balance</div>
            <div className="text-lg font-bold" style={{ color: "#fbbf24" }}>
              {state ? `${state.session.currency} ${state.session.balance.toFixed(2)}` : "—"}
            </div>
          </div>
        </header>

        {/* Countdown + Race */}
        <section className="px-4 py-3">
          <div className="rounded-2xl p-4" style={{ backgroundImage: "url('/moto/assets/png/moto_bg-c7ba0a1a.png')", backgroundSize: "cover", backgroundPosition: "center" }}>
            <div className="flex items-center justify-between">
              <div className="text-sm">Time left</div>
              <div className="text-3xl font-mono font-bold" style={{ color: closed ? "#ef4444" : "#fff" }}>
                {mm}:{ss}
              </div>
            </div>

            <div className="mt-4 space-y-1" style={{ background: "rgba(0,0,0,0.45)", padding: 8, borderRadius: 12 }}>
              {progress.map((p, i) => (
                <div key={i} className="relative h-6 rounded" style={{ background: "rgba(255,255,255,0.08)" }}>
                  <div
                    className="absolute top-0 flex h-6 items-center"
                    style={{ left: `${p}%`, transition: racing ? "left 240ms linear" : "none" }}
                  >
                    <img src="/moto/assets/png/moto-37464155.png" alt="" style={{ height: 22, filter: `hue-rotate(${i * 36}deg)` }} />
                    <span className="ml-1 text-[10px] font-bold" style={{ color: BIKE_COLORS[i] }}>#{i + 1}</span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Bet Type Tabs */}
        <section className="px-4">
          <div className="grid grid-cols-3 gap-2">
            {(["number", "color", "bigsmall"] as const).map((t) => (
              <button
                key={t}
                onClick={() => setTab(t)}
                className="rounded-lg py-2 text-sm font-semibold"
                style={{
                  background: tab === t ? "linear-gradient(180deg,#fbbf24,#f59e0b)" : "#1b2238",
                  color: tab === t ? "#000" : "#fff",
                }}
              >
                {t === "number" ? "Number" : t === "color" ? "Color" : "Big / Small"}
              </button>
            ))}
          </div>
        </section>

        {/* Amount */}
        <section className="px-4 py-3">
          <div className="flex items-center gap-2">
            <div className="text-xs opacity-70">Amount</div>
            <input
              type="number"
              min={1}
              value={amount}
              onChange={(e) => setAmount(Math.max(1, Number(e.target.value) || 0))}
              className="flex-1 rounded-lg bg-[#1b2238] px-3 py-2 text-sm"
            />
            {[10, 100, 1000].map((v) => (
              <button key={v} onClick={() => setAmount(v)} className="rounded-md bg-[#1b2238] px-2 py-1 text-xs">
                ₹{v}
              </button>
            ))}
          </div>
        </section>

        {/* Bet panel */}
        <section className="px-4 pb-4">
          {tab === "number" && (
            <div className="grid grid-cols-5 gap-2">
              {Array.from({ length: 10 }, (_, i) => i + 1).map((n) => {
                const colors = NUM_COLOR_LABEL(n);
                const bg = colors.length === 2 ? `linear-gradient(135deg,${colors[0] === "violet" ? "#a855f7" : "#22c55e"} 50%,${colors[1] === "red" ? "#ef4444" : "#22c55e"} 50%)` : (colors[0] === "red" ? "#ef4444" : "#22c55e");
                return (
                  <button
                    key={n}
                    disabled={closed || submitting}
                    onClick={() => placeBet("number", String(n))}
                    className="aspect-square rounded-xl text-xl font-bold disabled:opacity-50"
                    style={{ background: bg }}
                  >
                    {n}
                  </button>
                );
              })}
            </div>
          )}
          {tab === "color" && (
            <div className="grid grid-cols-3 gap-2">
              {[
                { v: "green", label: "Green ×2", c: "#22c55e" },
                { v: "violet", label: "Violet ×4.5", c: "#a855f7" },
                { v: "red", label: "Red ×2", c: "#ef4444" },
              ].map((b) => (
                <button
                  key={b.v}
                  disabled={closed || submitting}
                  onClick={() => placeBet("color", b.v)}
                  className="rounded-xl py-4 font-semibold disabled:opacity-50"
                  style={{ background: b.c }}
                >
                  {b.label}
                </button>
              ))}
            </div>
          )}
          {tab === "bigsmall" && (
            <div className="grid grid-cols-2 gap-2">
              <button disabled={closed || submitting} onClick={() => placeBet("bigsmall", "small")} className="rounded-xl bg-[#3b82f6] py-4 text-lg font-bold disabled:opacity-50">Small (1-5) ×2</button>
              <button disabled={closed || submitting} onClick={() => placeBet("bigsmall", "big")} className="rounded-xl bg-[#f97316] py-4 text-lg font-bold disabled:opacity-50">Big (6-10) ×2</button>
            </div>
          )}
        </section>

        {/* History tabs */}
        <section className="px-4 pb-8">
          <div className="mb-2 flex gap-2">
            {(["results", "mine"] as const).map((t) => (
              <button
                key={t}
                onClick={() => setHistoryTab(t)}
                className="rounded-md px-3 py-1 text-xs font-semibold"
                style={{ background: historyTab === t ? "#fbbf24" : "#1b2238", color: historyTab === t ? "#000" : "#fff" }}
              >
                {t === "results" ? "Results" : "My Bets"}
              </button>
            ))}
          </div>
          <div className="rounded-lg bg-[#1b2238] p-2 text-xs">
            {historyTab === "results" && (
              <div className="space-y-1">
                {(state?.recent_results ?? []).map((r) => (
                  <div key={r.period_no} className="flex items-center justify-between border-b border-white/5 py-1">
                    <span className="opacity-70">{r.period_no}</span>
                    <span className="font-bold" style={{ color: r.result_winner != null ? BIKE_COLORS[r.result_winner - 1] : "#fff" }}>
                      #{r.result_winner ?? "—"}
                    </span>
                  </div>
                ))}
                {(state?.recent_results.length ?? 0) === 0 && <div className="py-2 text-center opacity-50">No results yet</div>}
              </div>
            )}
            {historyTab === "mine" && (
              <div className="space-y-1">
                {(state?.my_bets ?? []).map((b) => (
                  <div key={b.id} className="flex items-center justify-between border-b border-white/5 py-1">
                    <div>
                      <div className="font-semibold">{b.motorace_periods.period_no}</div>
                      <div className="opacity-60">{b.bet_type}: {b.bet_value} · ₹{Number(b.amount).toFixed(2)}</div>
                    </div>
                    <div className="text-right">
                      <div className="font-bold" style={{ color: b.status === "won" ? "#22c55e" : b.status === "lost" ? "#ef4444" : "#fbbf24" }}>
                        {b.status.toUpperCase()}
                      </div>
                      {b.payout != null && b.payout > 0 && <div className="text-[10px]">+₹{Number(b.payout).toFixed(2)}</div>}
                    </div>
                  </div>
                ))}
                {(state?.my_bets.length ?? 0) === 0 && <div className="py-2 text-center opacity-50">No bets yet</div>}
              </div>
            )}
          </div>
        </section>

        {msg && (
          <div
            className="fixed bottom-4 left-1/2 -translate-x-1/2 rounded-lg px-4 py-2 text-sm font-semibold"
            style={{ background: msg.kind === "err" ? "#7f1d1d" : "#14532d", color: "#fff" }}
          >
            {msg.text}
          </div>
        )}
      </div>
    </main>
  );
}
