import { createFileRoute, useSearch } from "@tanstack/react-router";
import { useCallback, useEffect, useMemo, useState } from "react";

const A = "/moto/assets/png";

type StateResponse = {
  server_time: string;
  session: { display_name: string | null; balance: number; currency: string };
  current_period: { id: string; period_no: string; starts_at: string; ends_at: string; status: string };
  recent_results: Array<{ period_no: string; result_winner: number | null; settled_at: string }>;
  my_bets: Array<{
    id: string; period_id: string; bet_type: string; bet_value: string;
    amount: number; payout: number | null; status: string; created_at: string;
    motorace_periods: { period_no: string; result_winner: number | null };
  }>;
};

export const Route = createFileRoute("/motorace")({
  validateSearch: (s: Record<string, unknown>) => ({ token: (s.token as string) || "" }),
  component: MotoracePage,
  head: () => ({ meta: [{ title: "Moto Race" }] }),
});

const BIKE_NUMS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
const AMOUNT_CHIPS = [10, 100, 500, 1000];

function MotoracePage() {
  const { token } = useSearch({ from: "/motorace" });
  const [state, setState] = useState<StateResponse | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [amount, setAmount] = useState<number>(10);
  const [now, setNow] = useState(Date.now());
  const [placing, setPlacing] = useState(false);
  const [toast, setToast] = useState<string | null>(null);
  const [selected, setSelected] = useState<number | null>(null);

  const fetchState = useCallback(async () => {
    if (!token) return;
    try {
      const r = await fetch(`/api/public/v1/motorace/state?token=${token}`);
      if (!r.ok) {
        const j = await r.json().catch(() => ({}));
        setError(j.error || "load_failed"); return;
      }
      setState(await r.json()); setError(null);
    } catch (e) { setError(String(e)); }
  }, [token]);

  useEffect(() => { fetchState(); }, [fetchState]);
  useEffect(() => { const i = setInterval(() => setNow(Date.now()), 250); return () => clearInterval(i); }, []);
  useEffect(() => { const i = setInterval(fetchState, 3000); return () => clearInterval(i); }, [fetchState]);

  const endsMs = state ? new Date(state.current_period.ends_at).getTime() : 0;
  const secondsLeft = Math.max(0, Math.ceil((endsMs - now) / 1000));
  useEffect(() => {
    if (state && secondsLeft === 0) { const t = setTimeout(fetchState, 1500); return () => clearTimeout(t); }
  }, [secondsLeft, state, fetchState]);

  const placeBet = useCallback(async (betType: string, betValue: string) => {
    if (!state || placing) return;
    if (secondsLeft <= 3) { setToast("Round closing — wait for next"); setTimeout(() => setToast(null), 1800); return; }
    setPlacing(true);
    try {
      const r = await fetch("/api/public/v1/motorace/bet", {
        method: "POST",
        headers: { "content-type": "application/json", "x-session-token": token },
        body: JSON.stringify({ period_id: state.current_period.id, bet_type: betType, bet_value: betValue, amount }),
      });
      const j = await r.json();
      if (!r.ok) setToast(j.error || "Bet failed");
      else { setToast(`✓ Bet placed: ${betValue} · ₹${amount}`); fetchState(); }
    } finally { setPlacing(false); setTimeout(() => setToast(null), 2000); }
  }, [state, placing, secondsLeft, token, amount, fetchState]);

  const totalSeconds = 60;
  const progress = Math.min(100, Math.max(0, (secondsLeft / totalSeconds) * 100));
  const balanceFmt = useMemo(() => state ? `${state.session.balance.toLocaleString(undefined, { maximumFractionDigits: 2 })}` : "—", [state]);
  const lastWinner = state?.recent_results?.[0]?.result_winner ?? null;

  if (!token) {
    return (
      <div className="min-h-screen grid place-items-center p-6 text-center text-white"
           style={{ backgroundImage: `url(${A}/main_bg-8f7a16ef.png)`, backgroundSize: "cover", backgroundPosition: "center" }}>
        <div className="space-y-4 bg-black/60 p-6 rounded-2xl backdrop-blur">
          <img src={`${A}/motorace-card.png`} alt="Moto Race" className="mx-auto h-32 drop-shadow-2xl" />
          <h1 className="text-3xl font-black tracking-wider">MOTO RACE</h1>
          <p className="text-zinc-200 max-w-sm">Free demo session — ₹10,000 play balance</p>
          <button
            onClick={async () => {
              const r = await fetch("/api/public/v1/motorace/demo"); const j = await r.json();
              if (j.token) window.location.href = `/motorace?token=${j.token}`;
            }}
            className="rounded-full bg-gradient-to-b from-amber-300 to-amber-500 px-8 py-3 font-extrabold text-black shadow-xl"
          >▶ START DEMO</button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen text-white relative overflow-hidden"
         style={{ backgroundImage: `url(${A}/game_bg-ae3efd9e.png)`, backgroundSize: "cover", backgroundPosition: "top center", backgroundColor: "#1a0a2e" }}>
      <div className="mx-auto max-w-md min-h-screen flex flex-col pb-4 relative">
        {/* HEADER */}
        <header
          className="relative px-3 pt-3 pb-2"
          style={{ backgroundImage: `url(${A}/header_bg-7fdc110d.png)`, backgroundSize: "100% 100%" }}
        >
          <div className="flex items-center justify-between text-white">
            <div className="flex items-center gap-2">
              <img src={`${A}/car-b1876e4f.png`} className="h-8" alt="" />
              <div>
                <div className="text-[10px] uppercase tracking-widest opacity-80">Balance</div>
                <div className="text-lg font-extrabold tabular-nums">₹ {balanceFmt}</div>
              </div>
            </div>
            <button onClick={fetchState} className="rounded-full bg-white/15 backdrop-blur px-3 py-1 text-xs font-semibold">↻ Refresh</button>
          </div>
        </header>

        {/* PERIOD + TIMER */}
        <div className="mx-3 -mt-1 rounded-2xl px-4 py-3 flex items-center justify-between shadow-lg"
             style={{ background: "linear-gradient(180deg,#7d1e8c 0%,#3b0a4d 100%)", border: "1px solid #ff5fe533" }}>
          <div>
            <div className="text-[10px] uppercase tracking-widest text-pink-200/80">Period</div>
            <div className="font-mono font-bold text-base tracking-wider">{state?.current_period.period_no || "—"}</div>
          </div>
          <div className="text-right">
            <div className="text-[10px] uppercase tracking-widest text-pink-200/80">Countdown</div>
            <div className={`text-3xl font-black tabular-nums ${secondsLeft <= 5 ? "text-rose-300 animate-pulse" : "text-amber-300"}`}>
              {String(Math.floor(secondsLeft / 60)).padStart(2, "0")}:{String(secondsLeft % 60).padStart(2, "0")}
            </div>
          </div>
        </div>
        <div className="mx-3 mt-1 h-1.5 rounded-full bg-black/40 overflow-hidden">
          <div className="h-full bg-gradient-to-r from-fuchsia-400 via-amber-300 to-rose-500 transition-[width] duration-300" style={{ width: `${progress}%` }} />
        </div>

        {/* TRACK / PODIUM */}
        <section className="mx-3 mt-3 rounded-2xl p-3 relative overflow-hidden"
                 style={{ backgroundImage: `url(${A}/moto_bg-c7ba0a1a.png)`, backgroundSize: "cover", backgroundPosition: "center" }}>
          <div className="absolute inset-0 bg-gradient-to-b from-black/0 via-black/0 to-black/60 pointer-events-none" />
          <div className="relative flex items-end justify-center gap-2 h-44">
            {lastWinner ? (
              <div className="flex flex-col items-center">
                <img src={`${A}/motoR-f1cdad6d.png`} className="h-24 drop-shadow-2xl" alt="" />
                <div className="mt-1 rounded-full px-3 py-0.5 bg-amber-400 text-black font-extrabold text-sm">WINNER #{lastWinner}</div>
              </div>
            ) : (
              <div className="flex flex-col items-center">
                <img src={`${A}/moto-37464155.png`} className="h-24 drop-shadow-2xl animate-pulse" alt="" />
                <div className="mt-1 text-white/80 text-xs">Round in progress…</div>
              </div>
            )}
            <img src={`${A}/flame2-a96be4e8.png`} className="absolute right-1 bottom-1 h-10 opacity-80" alt="" />
          </div>
          <div className="relative mt-2 flex gap-1 justify-center">
            {(state?.recent_results || []).slice(0, 10).map(r => (
              <div key={r.period_no} title={r.period_no}
                   className="w-7 h-7 rounded-full grid place-items-center text-xs font-bold border border-white/30"
                   style={{ background: r.result_winner ? "linear-gradient(180deg,#fff2 0%,#0006 100%)" : "#222" }}>
                {r.result_winner ?? "?"}
              </div>
            ))}
          </div>
        </section>

        {/* AMOUNT */}
        <section className="mx-3 mt-3 rounded-2xl bg-black/40 backdrop-blur border border-white/10 p-3">
          <div className="text-[10px] uppercase tracking-widest text-pink-200/80 mb-2">Bet amount</div>
          <div className="flex gap-2">
            {AMOUNT_CHIPS.map(c => (
              <button key={c} onClick={() => setAmount(c)}
                      className={`flex-1 rounded-xl py-2 font-bold border transition ${amount === c ? "bg-amber-400 text-black border-amber-300 shadow-lg shadow-amber-500/30" : "bg-white/5 border-white/15 text-white/80"}`}>
                ₹{c}
              </button>
            ))}
          </div>
        </section>

        {/* BIKES */}
        <section className="mx-3 mt-3">
          <div className="flex items-center justify-between mb-2">
            <div className="text-[10px] uppercase tracking-widest text-pink-200/80">Pick your bike — 9× payout</div>
            <img src={`${A}/rank-2718dc26.png`} className="h-4 opacity-70" alt="" />
          </div>
          <div className="grid grid-cols-5 gap-2">
            {BIKE_NUMS.map(n => (
              <button key={n}
                      onClick={() => { setSelected(n); placeBet("number", String(n)); }}
                      disabled={placing}
                      className={`aspect-square rounded-2xl p-2 flex flex-col items-center justify-end relative overflow-hidden border transition active:scale-95 ${selected === n ? "border-amber-300 ring-2 ring-amber-300/50" : "border-white/15"}`}
                      style={{ background: "linear-gradient(180deg,#4a1b7e 0%,#1a0633 100%)" }}>
                <img src={`${A}/n_${n}-${["c496ebcf","19cfb73b","60b21ff1","1afafc4f","a0074aed","7acd9687","3fa63e7c","4463784f","b8adf2e1","a8e5582a"][n-1]}.png`}
                     className="h-7 absolute top-1.5 left-1/2 -translate-x-1/2" alt="" />
                <img src={`${A}/moto-37464155.png`} className="h-8 mt-4" alt="" />
              </button>
            ))}
          </div>
        </section>

        {/* COLOR / BIG-SMALL */}
        <section className="mx-3 mt-3 grid grid-cols-3 gap-2">
          <button onClick={() => placeBet("color", "green")} disabled={placing}
                  className="rounded-xl py-3 font-bold text-white shadow-lg disabled:opacity-60"
                  style={{ background: "linear-gradient(180deg,#22c97a,#0e7a47)" }}>
            Green<div className="text-[10px] opacity-80">2×</div>
          </button>
          <button onClick={() => placeBet("color", "violet")} disabled={placing}
                  className="rounded-xl py-3 font-bold text-white shadow-lg disabled:opacity-60"
                  style={{ background: "linear-gradient(180deg,#a14bff,#5a1d99)" }}>
            Violet<div className="text-[10px] opacity-80">4.5×</div>
          </button>
          <button onClick={() => placeBet("color", "red")} disabled={placing}
                  className="rounded-xl py-3 font-bold text-white shadow-lg disabled:opacity-60"
                  style={{ background: "linear-gradient(180deg,#ff4d6d,#a3022b)" }}>
            Red<div className="text-[10px] opacity-80">2×</div>
          </button>
        </section>
        <section className="mx-3 mt-2 grid grid-cols-2 gap-2">
          <button onClick={() => placeBet("bigsmall", "small")} disabled={placing}
                  className="rounded-xl py-3 font-bold border border-amber-400/40 bg-black/40 text-amber-200">
            SMALL (1–5) <span className="text-[10px] opacity-70">2×</span>
          </button>
          <button onClick={() => placeBet("bigsmall", "big")} disabled={placing}
                  className="rounded-xl py-3 font-bold border border-amber-400/40 bg-black/40 text-amber-200">
            BIG (6–10) <span className="text-[10px] opacity-70">2×</span>
          </button>
        </section>

        {/* MY BETS */}
        <section className="mx-3 mt-3 rounded-2xl bg-black/40 backdrop-blur border border-white/10 p-3">
          <div className="text-[10px] uppercase tracking-widest text-pink-200/80 mb-2">My recent bets</div>
          <div className="space-y-1.5 max-h-56 overflow-y-auto">
            {(state?.my_bets || []).map(b => (
              <div key={b.id} className="flex items-center justify-between text-sm bg-white/5 rounded-lg px-3 py-1.5">
                <div>
                  <div className="font-mono text-[10px] text-pink-200/70">{b.motorace_periods.period_no}</div>
                  <div className="font-semibold capitalize text-white">
                    {b.bet_type === "number" ? `#${b.bet_value}` : b.bet_value}
                    <span className="text-white/60 font-normal ml-1">· ₹{b.amount}</span>
                  </div>
                </div>
                <div className={`text-sm font-extrabold ${b.status === "won" ? "text-emerald-300" : b.status === "lost" ? "text-rose-300" : "text-amber-300"}`}>
                  {b.status === "pending" ? "Pending" : b.status === "won" ? `+₹${b.payout}` : "Lost"}
                </div>
              </div>
            ))}
            {(!state?.my_bets || state.my_bets.length === 0) && (
              <div className="text-pink-200/50 text-sm py-2 text-center">No bets yet — tap a bike to play.</div>
            )}
          </div>
        </section>

        {error && <div className="fixed inset-x-0 top-4 mx-auto w-fit rounded-lg bg-rose-600 px-3 py-2 text-sm shadow-lg z-50">{error}</div>}
        {toast && <div className="fixed inset-x-0 bottom-6 mx-auto w-fit rounded-full bg-black/80 border border-amber-400/40 px-4 py-2 text-sm shadow-2xl z-50">{toast}</div>}
      </div>
    </div>
  );
}
