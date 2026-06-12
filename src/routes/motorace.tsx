import { createFileRoute, useSearch } from "@tanstack/react-router";
import { useCallback, useEffect, useMemo, useState } from "react";

const A = "/moto/assets/png";
const DIGIT = ["099c271b", "f5111f40", "df3776a5", "e91a8bdf", "9eb01c43", "d943a6b9", "23e7c820", "7d6f0493", "8ba654a9", "dd7839fc"];
const BALL = ["028fb9ed", "b8b23c72", "395f2cff", "8d4c1629", "06e35201", "e5e81db8", "d9bd2794", "9d781b7c", "ee496cf1", "1592efe4"];
const NO = ["c496ebcf", "19cfb73b", "60b21ff1", "1afafc4f", "a0074aed", "7acd9687", "3fa63e7c", "4463784f", "b8adf2e1", "a8e5582a"];
const NUMBERS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

type StateResponse = {
  session: { display_name: string | null; balance: number; currency: string };
  current_period: { id: string; period_no: string; ends_at: string; status: string };
  recent_results: Array<{ period_no: string; result_winner: number | null; settled_at: string }>;
  my_bets: Array<{ id: string; bet_type: string; bet_value: string; amount: number; payout: number | null; status: string; motorace_periods: { period_no: string; result_winner: number | null } }>;
};

export const Route = createFileRoute("/motorace")({
  validateSearch: (s: Record<string, unknown>) => ({ token: (s.token as string) || "" }),
  component: MotoracePage,
  head: () => ({ meta: [{ title: "MotoRace 1Min" }] }),
});

const AMOUNTS = [10, 100, 500, 1000];

function MotoracePage() {
  const { token } = useSearch({ from: "/motorace" });
  const [state, setState] = useState<StateResponse | null>(null);
  const [now, setNow] = useState(Date.now());
  const [tab, setTab] = useState(1);
  const [amount, setAmount] = useState(10);
  const [placing, setPlacing] = useState(false);
  const [toast, setToast] = useState("Loading demo…");
  const [lastSettled, setLastSettled] = useState<string | null>(null);

  useEffect(() => {
    if (token) return;
    fetch("/api/public/v1/motorace/demo")
      .then((r) => r.json())
      .then((j) => { if (j.token) window.location.href = `/motorace?token=${j.token}`; })
      .catch(() => setToast("Demo could not start"));
  }, [token]);

  const fetchState = useCallback(async () => {
    if (!token) return;
    const r = await fetch(`/api/public/v1/motorace/state?token=${token}`);
    const j = await r.json();
    if (r.ok) {
      setState(j);
      const top = j.recent_results?.[0];
      if (top && top.period_no !== lastSettled) {
        if (lastSettled !== null) {
          const myWin = j.my_bets?.find((b: { motorace_periods: { period_no: string }; status: string; payout: number }) => b.motorace_periods.period_no === top.period_no && b.status === "won");
          setToast(myWin ? `🏆 You won ₹${Number(myWin.payout).toFixed(0)}! Winner: ${top.result_winner}` : `Winner: ${top.result_winner}`);
          setTimeout(() => setToast(""), 3500);
        }
        setLastSettled(top.period_no);
      }
    }
  }, [token, lastSettled]);

  useEffect(() => { fetchState(); }, [fetchState]);
  useEffect(() => { const i = setInterval(() => setNow(Date.now()), 250); return () => clearInterval(i); }, []);
  useEffect(() => { const i = setInterval(fetchState, 2000); return () => clearInterval(i); }, [fetchState]);


  const secondsLeft = Math.max(0, Math.ceil(((state ? new Date(state.current_period.ends_at).getTime() : now + 60000) - now) / 1000));
  const showRace = secondsLeft <= 20;
  const issue = state?.current_period.period_no || "2026061210001907";
  const last = state?.recent_results?.[0]?.result_winner || 4;
  const topThree = useMemo(() => {
    const rest = NUMBERS.filter((n) => n !== last);
    return [last, rest[(last + 1) % rest.length], rest[(last + 4) % rest.length]];
  }, [last]);
  // Pre-generated random lane speeds per period so each race looks unique but stable
  const laneSpeeds = useMemo(() => {
    const seed = state?.current_period.id || "default";
    let h = 0; for (const c of seed) h = (h * 31 + c.charCodeAt(0)) >>> 0;
    return NUMBERS.map((_, i) => {
      h = (h * 1103515245 + 12345) >>> 0;
      return 0.85 + ((h % 1000) / 1000) * 0.35; // 0.85 - 1.20 speed multiplier
    });
  }, [state?.current_period.id]);


  const placeBet = async (betType: string, betValue: string) => {
    if (!state || !token || placing) return;
    setPlacing(true);
    const r = await fetch("/api/public/v1/motorace/bet", {
      method: "POST",
      headers: { "content-type": "application/json", "x-session-token": token },
      body: JSON.stringify({ period_id: state.current_period.id, bet_type: betType, bet_value: betValue.toLowerCase(), amount }),
    });
    const j = await r.json().catch(() => ({}));
    setToast(r.ok ? `Bet placed ₹${amount}` : j.error || "Bet failed");
    setPlacing(false);
    fetchState();
    setTimeout(() => setToast(""), 1800);
  };

  return (
    <main className="min-h-screen" style={{ background: "#9195a3", fontFamily: "Arial, sans-serif" }}>
      <div className="mx-auto min-h-screen overflow-hidden" style={{ maxWidth: 400, background: "#120d14", color: "#fff" }}>
        <header className="relative flex h-[46px] items-center justify-between px-4" style={{ background: "#0b070e" }}>
          <button className="text-[30px] leading-none" onClick={() => history.back()} style={{ color: "#ffdf62" }}>‹</button>
          <h1 className="text-[18px] font-normal">MotoRace 1Min</h1>
          <span className="text-[14px] font-bold" style={{ color: "#ffdf62" }}>₹{(state?.session.balance ?? 0).toLocaleString()}</span>
        </header>

        <section className="relative h-[258px] overflow-hidden">
          {showRace ? <RaceStage secondsLeft={secondsLeft} laneSpeeds={laneSpeeds} /> : <WaitingStage issue={issue} secondsLeft={secondsLeft} topThree={topThree} />}
          <img src={`${A}/sound-134fdaf8.png`} alt="sound" className="absolute right-[18px] top-[14px] h-[17px] w-[17px]" />
          <div className="absolute bottom-0 left-0 right-0 flex h-[42px] items-center gap-2 px-3 text-[13px]" style={{ backgroundImage: `url(${A}/bottom-1ef06fc2.png)`, backgroundSize: "100% 100%" }}>
            <span className="mr-auto font-mono">{issue}</span>
            {topThree.map((n, i) => <RankPill key={i} label={`${i + 1}${i === 0 ? "st" : i === 1 ? "nd" : "rd"}`} n={n} />)}
            <span className="text-[18px] text-[#dbeaff]">⌄</span>
          </div>
        </section>

        <section className="mx-auto mt-[26px] w-[93%] rounded-[13px] pb-8" style={{ background: "#21191d" }}>
          <div className="relative h-[62px] rounded-t-[16px] px-4 pt-[15px]" style={{ background: "#ffdc48" }}>
            <img src={`${A}/header-74fbae03.png`} alt="" className="absolute inset-0 h-full w-full rounded-t-[16px]" />
            <img src={`${A}/moto_bg-c7ba0a1a.png`} alt="" className="absolute -right-1 -top-[18px] h-[54px] w-[89px] object-cover" />
            <h2 className="relative text-[20px] font-bold text-black">Betting Area</h2>
          </div>
          <div className="px-[14px] pt-[7px]">
            <div className="flex gap-6 text-[16px] font-bold">
              {[1, 2, 3].map((x) => <button key={x} onClick={() => setTab(x)} style={{ color: tab === x ? "#fff" : "#c7b7b2" }}>{x}{x === 1 ? "st" : x === 2 ? "nd" : "rd"} Number</button>)}
            </div>
            <p className="mt-1 text-[14px]">Select {tab}st number <span style={{ color: "#fb5b5b" }}>(Odds 9X)</span></p>
            <div className="mt-2 flex items-center gap-2">
              <span className="text-[12px] text-[#c7b7b2]">Amount</span>
              {AMOUNTS.map((a) => (
                <button key={a} onClick={() => setAmount(a)} className="rounded-full px-3 py-1 text-[12px] font-bold" style={{ background: amount === a ? "#ffdc48" : "#2a2026", color: amount === a ? "#000" : "#fff" }}>₹{a}</button>
              ))}
            </div>
            <div className="mt-[15px] grid grid-cols-5 gap-x-[16px] gap-y-[18px]">
              {NUMBERS.map((n) => <button key={n} disabled={placing} onClick={() => placeBet("number", String(n))}><img src={`${A}/ball_${n}-${BALL[n - 1]}.png`} alt={`${n}`} /></button>)}
            </div>
            <BetSection title="Odd or Even" subtitle="Select the rank number as odd or even" rows={["1st", "2st", "3st"]} left="Odd" right="Even" onPick={(v) => placeBet("bigsmall", v === "Odd" ? "big" : "small")} />
            <BetSection title="Big or Small" subtitle="Select the rank number as Big (6&over) or Small (under 6)" rows={["1st", "2st", "3st"]} left="Big" right="Small" onPick={(v) => placeBet("bigsmall", v)} />
          </div>
        </section>

        <section className="mx-auto mt-4 w-[93%] rounded-[13px] p-3" style={{ background: "#21191d" }}>
          <h3 className="mb-2 text-[14px] font-bold text-[#ffdc48]">Recent Results</h3>
          <div className="flex flex-wrap gap-1">
            {(state?.recent_results ?? []).slice(0, 10).map((r) => (
              <span key={r.period_no} className="flex h-7 w-7 items-center justify-center rounded-full text-[12px] font-bold" style={{ background: r.result_winner && r.result_winner >= 6 ? "#dc2626" : "#16a34a", color: "#fff" }}>{r.result_winner}</span>
            ))}
            {!state?.recent_results?.length ? <span className="text-[12px] text-[#c7b7b2]">No results yet — wait for the timer.</span> : null}
          </div>
        </section>
        {state?.my_bets?.length ? (
          <section className="mx-auto mt-3 w-[93%] rounded-[13px] p-3" style={{ background: "#21191d" }}>
            <h3 className="mb-2 text-[14px] font-bold text-[#ffdc48]">My Bets</h3>
            <div className="space-y-1 text-[12px]">
              {state.my_bets.slice(0, 8).map((b) => (
                <div key={b.id} className="flex items-center justify-between border-b border-white/5 pb-1">
                  <span className="font-mono text-[10px] text-[#c7b7b2]">{b.motorace_periods.period_no.slice(-6)}</span>
                  <span>{b.bet_type}:{b.bet_value}</span>
                  <span>₹{b.amount}</span>
                  <span style={{ color: b.status === "won" ? "#22c55e" : b.status === "lost" ? "#ef4444" : "#ffdc48" }}>
                    {b.status === "pending" ? "…" : b.status === "won" ? `+₹${Number(b.payout).toFixed(0)}` : "lost"}
                  </span>
                </div>
              ))}
            </div>
          </section>
        ) : null}
        {toast ? <div className="fixed bottom-5 left-1/2 z-50 -translate-x-1/2 rounded-full bg-black/80 px-4 py-2 text-sm text-white">{toast}</div> : null}
      </div>
    </main>
  );
}

function WaitingStage({ issue, secondsLeft, topThree }: { issue: string; secondsLeft: number; topThree: number[] }) {
  const nums = String(Math.max(0, secondsLeft - 23)).padStart(2, "0").slice(-2);
  return <div className="relative h-full" style={{ backgroundImage: `url(${A}/show_bg-4119db52.png)`, backgroundSize: "100% 216px", backgroundRepeat: "no-repeat" }}>
    <span className="absolute left-[14px] top-[18px] text-[12px]">{issue}</span>
    <div className="absolute left-1/2 top-0 flex h-[47px] w-[126px] -translate-x-1/2 flex-col items-center justify-center" style={{ backgroundImage: `url(${A}/time_bg-4ccbe78f.png)`, backgroundSize: "100% 100%" }}>
      <span className="text-[12px]">Time remaining</span>
      <div className="flex items-center gap-[1px]"><img src={`${A}/t_0-${DIGIT[0]}.png`} className="h-[18px] w-[12px]" /><img src={`${A}/t_0-${DIGIT[0]}.png`} className="h-[18px] w-[12px]" /><span className="text-red-500">:</span>{nums.split("").map((d, i) => <img key={i} src={`${A}/t_${d}-${DIGIT[Number(d)]}.png`} className="h-[18px] w-[12px]" />)}</div>
    </div>
    <div className="absolute left-[14px] top-[50px] flex flex-col gap-[5px]">{[1, 2, 3, 4, 5].map((n) => <img key={n} src={`${A}/n_${n}-${NO[n - 1]}.png`} className="h-[28px] w-[38px] rounded border border-[#2ee9ff]" />)}</div>
    <img src={`${A}/moto_bg-c7ba0a1a.png`} alt="moto racer" className="absolute left-[75px] top-[55px] h-[144px] w-[190px] object-cover" />
    <div className="absolute right-[13px] top-[65px] h-[140px] w-[128px] px-4 pt-4 text-blue-700" style={{ backgroundImage: `url(${A}/rank_bg-33383471.png)`, backgroundSize: "100% 100%" }}><div className="text-right text-[14px] text-white">NO.5</div><div className="text-[15px]">Last 100 races</div><div className="mt-3 text-[14px] text-black">1st <span className="ml-2 text-red-500">{topThree[0]}</span></div><img src={`${A}/rb_1-b317aa0b.png`} className="mt-1 h-[26px] w-[106px]" /></div>
  </div>;
}

function RaceStage({ issue, topThree }: { issue: string; topThree: number[] }) {
  return <div className="relative h-full" style={{ background: "#111" }}>
    <div className="absolute left-0 top-[8px] flex h-[29px] items-center gap-[2px] px-4"><img src={`${A}/number-3f825650.png`} className="h-[27px] w-[180px]" /><span className="ml-1 text-[16px] font-bold">1st</span></div>
    <img src={`${A}/track3_1-cf31d304.png`} className="absolute left-0 top-[46px] h-[167px] w-full object-cover" />
    <img src={`${A}/car-b1876e4f.png`} className="absolute left-[13px] top-[80px] h-[145px] w-[22px] object-fill" />
    <div className="absolute left-1/2 top-[116px] h-[28px] w-[65px] -translate-x-1/2 rounded-full border bg-black/70"><span className="absolute left-[8px] top-[5px] h-[18px] w-[18px] rounded-full bg-red-600" /></div>
  </div>;
}

function RankPill({ label, n }: { label: string; n: number }) {
  return <span className="flex items-center gap-1 text-[16px] font-bold"><span>{label}</span><img src={`${A}/n_${n}-${NO[n - 1]}.png`} className="h-[28px] w-[28px] rounded-[5px]" /></span>;
}

function BetSection({ title, subtitle, rows, left, right, onPick }: { title: string; subtitle: string; rows: string[]; left: string; right: string; onPick: (v: string) => void }) {
  return <div className="mt-[18px]"><h3 className="text-[17px] font-bold text-white" style={{ textShadow: "1px 1px #000, -1px -1px #000" }}>{title}</h3><p className="text-[14px]">{subtitle}</p><div className="mt-[16px] space-y-[28px]">{rows.map((r) => <div key={r} className="grid grid-cols-[70px_1fr_1fr] items-start"><span className="text-[16px] font-bold text-white" style={{ textShadow: "1px 1px #000" }}>{r}</span><button onClick={() => onPick(left)} className="text-left"><span className="block text-[16px] font-bold text-[#f0c8bd] leading-none">{left}</span><span className="block text-[14px] text-[#8e7975] leading-none">2X</span></button><button onClick={() => onPick(right)} className="text-left"><span className="block text-[16px] font-bold text-[#f0c8bd] leading-none">{right}</span><span className="block text-[14px] text-[#8e7975] leading-none">2X</span></button></div>)}</div></div>;
}
