import { createFileRoute } from "@tanstack/react-router";
import { useEffect, useMemo, useRef, useState } from "react";

export const Route = createFileRoute("/motorace")({
  head: () => ({
    meta: [
      { title: "Moto Race — Live Number Racing Game" },
      { name: "description", content: "Bet on cars 1-10. New race every minute. Win up to 9x your bet." },
    ],
  }),
  component: MotoracePage,
});

const ROUND_SECONDS = 60;
const CARS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10] as const;
const COLOR: Record<number, "red" | "green" | "violet"> = {
  0: "red", 1: "green", 2: "red", 3: "green", 4: "red",
  5: "green", 6: "red", 7: "green", 8: "red", 9: "green", 10: "violet",
};
const CAR_HEX: Record<number, string> = {
  1: "#ef4444", 2: "#f97316", 3: "#eab308", 4: "#22c55e", 5: "#06b6d4",
  6: "#3b82f6", 7: "#8b5cf6", 8: "#ec4899", 9: "#14b8a6", 10: "#a855f7",
};

type Bet = { kind: "number" | "color" | "size"; value: string; amount: number };
type Result = { period: string; winner: number; podium: number[] };

function periodId(d = new Date()) {
  const m = Math.floor(d.getTime() / (ROUND_SECONDS * 1000));
  return `MR${m}`;
}
function secondsLeft() {
  return ROUND_SECONDS - Math.floor((Date.now() / 1000) % ROUND_SECONDS);
}

function MotoracePage() {
  const [now, setNow] = useState(Date.now());
  const [balance, setBalance] = useState<number>(() => {
    if (typeof window === "undefined") return 1000;
    return Number(localStorage.getItem("mr_bal") ?? 1000);
  });
  const [period, setPeriod] = useState(periodId());
  const [bets, setBets] = useState<Bet[]>([]);
  const [pending, setPending] = useState<Bet[]>([]);
  const [history, setHistory] = useState<Result[]>([]);
  const [racing, setRacing] = useState(false);
  const [progress, setProgress] = useState<number[]>(() => CARS.map(() => 0));
  const [winner, setWinner] = useState<number | null>(null);
  const [toast, setToast] = useState<string | null>(null);
  const [amount, setAmount] = useState(10);
  const settledRef = useRef<Set<string>>(new Set());

  useEffect(() => {
    localStorage.setItem("mr_bal", String(balance));
  }, [balance]);

  // tick
  useEffect(() => {
    const t = setInterval(() => setNow(Date.now()), 250);
    return () => clearInterval(t);
  }, []);

  const left = useMemo(() => secondsLeft(), [now]);
  const lockPhase = left <= 5;

  // round transitions
  useEffect(() => {
    const current = periodId();
    if (current !== period) {
      // settle previous round
      const w = Math.floor(Math.random() * 10) + 1;
      const others = CARS.filter((c) => c !== w);
      for (let i = others.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [others[i], others[j]] = [others[j], others[i]];
      }
      const podium = [w, others[0], others[1]];

      if (!settledRef.current.has(period)) {
        settledRef.current.add(period);
        let payout = 0;
        const detail: string[] = [];
        for (const b of bets) {
          let win = 0;
          if (b.kind === "number" && Number(b.value) === w) win = b.amount * 9;
          else if (b.kind === "color") {
            if (b.value === COLOR[w] && w !== 10 && w !== 0) win = b.amount * 2;
            else if (b.value === "violet" && (w === 0 || w === 10)) win = b.amount * 4.5;
          } else if (b.kind === "size") {
            const big = w >= 5;
            if ((b.value === "big" && big) || (b.value === "small" && !big)) win = b.amount * 2;
          }
          if (win > 0) {
            payout += win;
            detail.push(`+₹${win.toFixed(0)} on ${b.kind}:${b.value}`);
          }
        }
        if (payout > 0) {
          setBalance((b) => b + payout);
          setToast(`You won ₹${payout.toFixed(0)}! ${detail.join(", ")}`);
        } else if (bets.length > 0) {
          setToast(`No win this round. Better luck next race!`);
        }
      }

      // race animation
      setRacing(true);
      setWinner(null);
      setProgress(CARS.map(() => 0));
      const start = Date.now();
      const duration = 3000;
      const speeds = CARS.map((c) => (c === w ? 1 + Math.random() * 0.05 : 0.78 + Math.random() * 0.18));
      const anim = setInterval(() => {
        const t = (Date.now() - start) / duration;
        if (t >= 1) {
          clearInterval(anim);
          setProgress(speeds.map((s) => Math.min(1, s)));
          setRacing(false);
          setWinner(w);
          setHistory((h) => [{ period, winner: w, podium }, ...h].slice(0, 20));
        } else {
          setProgress(speeds.map((s) => Math.min(1, s * t)));
        }
      }, 50);

      setBets(pending);
      setPending([]);
      setPeriod(current);
    }
  }, [now, period, bets, pending]);

  useEffect(() => {
    if (!toast) return;
    const t = setTimeout(() => setToast(null), 4000);
    return () => clearTimeout(t);
  }, [toast]);

  const placeBet = (kind: Bet["kind"], value: string) => {
    if (amount <= 0 || amount > balance) {
      setToast("Insufficient balance");
      return;
    }
    if (lockPhase) {
      setToast("Round locked. Bet placed for next race.");
      setPending((p) => [...p, { kind, value, amount }]);
    } else {
      setBets((b) => [...b, { kind, value, amount }]);
    }
    setBalance((b) => b - amount);
  };

  return (
    <div className="min-h-screen bg-gradient-to-b from-[#0a0e27] via-[#1a0b2e] to-[#0a0e27] text-white">
      {/* Header */}
      <div className="sticky top-0 z-20 backdrop-blur bg-black/40 border-b border-white/10">
        <div className="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between">
          <h1 className="text-lg font-bold tracking-wide">🏁 MOTO RACE</h1>
          <div className="flex items-center gap-2 bg-yellow-500/15 border border-yellow-400/40 px-3 py-1.5 rounded-full">
            <span className="text-yellow-300 text-xs">Balance</span>
            <span className="font-bold text-yellow-200">₹{balance.toFixed(2)}</span>
          </div>
        </div>
      </div>

      <div className="max-w-3xl mx-auto px-4 py-4 space-y-4">
        {/* Period + Timer */}
        <div className="grid grid-cols-2 gap-3">
          <div className="bg-white/5 border border-white/10 rounded-xl p-4">
            <div className="text-xs text-white/50">Period</div>
            <div className="font-mono font-bold text-lg mt-1">{period}</div>
          </div>
          <div className={`rounded-xl p-4 border ${lockPhase ? "bg-red-500/10 border-red-400/40" : "bg-emerald-500/10 border-emerald-400/40"}`}>
            <div className="text-xs text-white/60">{lockPhase ? "Bets Locked" : "Time Left"}</div>
            <div className="font-mono font-bold text-3xl mt-1 tabular-nums">
              {String(Math.floor(left / 60)).padStart(2, "0")}:{String(left % 60).padStart(2, "0")}
            </div>
          </div>
        </div>

        {/* Race track */}
        <div className="bg-gradient-to-b from-black/60 to-black/30 border border-white/10 rounded-2xl p-3 overflow-hidden">
          <div className="text-xs text-white/50 mb-2 px-1">
            {racing ? "🏎 Racing..." : winner ? `🏆 Winner: Car ${winner}` : "Waiting for next race"}
          </div>
          <div className="space-y-1.5">
            {CARS.map((c, i) => (
              <div key={c} className="flex items-center gap-2">
                <div className="w-6 text-xs text-white/60 font-mono">#{c}</div>
                <div className="relative flex-1 h-7 bg-white/5 rounded-md overflow-hidden border border-white/5">
                  <div
                    className="absolute inset-y-0 left-0 transition-all duration-100 ease-linear flex items-center justify-end pr-2"
                    style={{
                      width: `${(progress[i] ?? 0) * 100}%`,
                      background: `linear-gradient(90deg, transparent, ${CAR_HEX[c]}aa)`,
                    }}
                  >
                    <span className="text-base">🏍</span>
                  </div>
                  {winner === c && !racing && (
                    <div className="absolute inset-0 flex items-center justify-center text-xs font-bold text-yellow-300 bg-yellow-500/10">
                      WINNER
                    </div>
                  )}
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Amount selector */}
        <div className="bg-white/5 border border-white/10 rounded-xl p-3">
          <div className="flex items-center justify-between mb-2">
            <span className="text-xs text-white/60">Bet Amount</span>
            <span className="font-bold text-yellow-300">₹{amount}</span>
          </div>
          <div className="flex gap-2 flex-wrap">
            {[10, 50, 100, 500, 1000].map((a) => (
              <button
                key={a}
                onClick={() => setAmount(a)}
                className={`px-3 py-1.5 rounded-lg text-sm font-semibold transition ${
                  amount === a
                    ? "bg-yellow-400 text-black"
                    : "bg-white/10 text-white hover:bg-white/20"
                }`}
              >
                ₹{a}
              </button>
            ))}
          </div>
        </div>

        {/* Color bets */}
        <div className="grid grid-cols-3 gap-2">
          <button
            onClick={() => placeBet("color", "green")}
            className="py-3 rounded-xl font-bold text-white bg-gradient-to-b from-emerald-500 to-emerald-700 hover:brightness-110 shadow-lg shadow-emerald-900/40"
          >
            Green <span className="text-xs opacity-80">2x</span>
          </button>
          <button
            onClick={() => placeBet("color", "violet")}
            className="py-3 rounded-xl font-bold text-white bg-gradient-to-b from-violet-500 to-violet-700 hover:brightness-110 shadow-lg shadow-violet-900/40"
          >
            Violet <span className="text-xs opacity-80">4.5x</span>
          </button>
          <button
            onClick={() => placeBet("color", "red")}
            className="py-3 rounded-xl font-bold text-white bg-gradient-to-b from-red-500 to-red-700 hover:brightness-110 shadow-lg shadow-red-900/40"
          >
            Red <span className="text-xs opacity-80">2x</span>
          </button>
        </div>

        {/* Number bets */}
        <div className="grid grid-cols-5 gap-2">
          {CARS.map((c) => (
            <button
              key={c}
              onClick={() => placeBet("number", String(c))}
              className="aspect-square rounded-xl font-bold text-lg text-white shadow-lg transition hover:scale-105"
              style={{
                background: `linear-gradient(135deg, ${CAR_HEX[c]}, ${CAR_HEX[c]}99)`,
              }}
            >
              {c}
              <div className="text-[10px] opacity-80 font-normal">9x</div>
            </button>
          ))}
        </div>

        {/* Size bets */}
        <div className="grid grid-cols-2 gap-2">
          <button
            onClick={() => placeBet("size", "small")}
            className="py-3 rounded-l-2xl rounded-r-md font-bold bg-gradient-to-r from-orange-500 to-amber-500 text-white shadow-lg"
          >
            SMALL (1-4) <span className="text-xs opacity-80">2x</span>
          </button>
          <button
            onClick={() => placeBet("size", "big")}
            className="py-3 rounded-r-2xl rounded-l-md font-bold bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-lg"
          >
            BIG (5-9) <span className="text-xs opacity-80">2x</span>
          </button>
        </div>

        {/* Active bets */}
        {(bets.length > 0 || pending.length > 0) && (
          <div className="bg-white/5 border border-white/10 rounded-xl p-3">
            <div className="text-xs text-white/60 mb-2">Active Bets</div>
            <div className="flex flex-wrap gap-1.5">
              {bets.map((b, i) => (
                <span key={`b${i}`} className="text-xs bg-emerald-500/20 border border-emerald-400/30 px-2 py-1 rounded-full">
                  {b.kind}:{b.value} ₹{b.amount}
                </span>
              ))}
              {pending.map((b, i) => (
                <span key={`p${i}`} className="text-xs bg-amber-500/20 border border-amber-400/30 px-2 py-1 rounded-full">
                  next · {b.kind}:{b.value} ₹{b.amount}
                </span>
              ))}
            </div>
          </div>
        )}

        {/* History */}
        <div className="bg-white/5 border border-white/10 rounded-xl p-3">
          <div className="text-xs text-white/60 mb-2">Recent Results</div>
          {history.length === 0 ? (
            <div className="text-xs text-white/40 py-4 text-center">No races yet</div>
          ) : (
            <div className="space-y-1.5">
              {history.map((r) => (
                <div key={r.period} className="flex items-center justify-between text-sm bg-black/20 rounded-lg px-3 py-2">
                  <span className="font-mono text-xs text-white/60">{r.period}</span>
                  <div className="flex items-center gap-2">
                    {r.podium.map((p, i) => (
                      <span
                        key={i}
                        className="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white"
                        style={{ background: CAR_HEX[p], opacity: i === 0 ? 1 : 0.55 }}
                      >
                        {p}
                      </span>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>

        <div className="text-center text-xs text-white/30 pb-6">
          For entertainment only · Demo balance · 18+
        </div>
      </div>

      {toast && (
        <div className="fixed bottom-4 left-1/2 -translate-x-1/2 z-30 bg-black/90 border border-yellow-400/40 text-yellow-200 px-4 py-2 rounded-full text-sm shadow-2xl">
          {toast}
        </div>
      )}
    </div>
  );
}
