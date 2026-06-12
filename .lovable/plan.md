## Goal

A working Moto Race game running entirely on your Lovable Cloud — your accounts, your balances, your bets, your settlements. No dependency on joshgame.online.

We'll do Moto Race first (this session). Once it's working end-to-end, we repeat the same pattern for WinGo, K3, 5D, Trx in follow-up sessions.

## Scope of this session

### 1. Auth

- auth will be done by the api of my clients every data they will provide by token like balance and user id all ok

### 2. Database (new tables)

- `profiles` — user_id, username, balance (numeric), created_at. Auto-created on signup via trigger.
- `motorace_periods` — period_no (e.g. `MR20260612001`), starts_at, ends_at, status (`open` | `locked` | `settled`), result_winner (1–10), created_at. One row every 60 seconds.
- `motorace_bets` — user_id, period_id, bet_type (`number` | `color` | `bigsmall`), bet_value (e.g. `7`, `green`, `big`), amount, payout (nullable until settled), status (`pending` | `won` | `lost`), created_at.
- All under RLS: users only see/insert their own bets; periods readable by all authenticated users.

### 3. Server functions (createServerFn)

- `getCurrentPeriod()` — returns the open period + seconds left.
- `getRecentResults(limit)` — last 20 settled periods for the history strip.
- `getMyBets(periodId?)` — user's bet history.
- `placeBet({ periodId, betType, betValue, amount })` — atomic: checks period still open, deducts from balance, inserts bet row. Refuses if locked or insufficient balance.
- `getMyBalance()` — current user balance.

### 4. Settlement (edge function on cron)

- Every 60s a scheduled job (`pg_cron` hitting an edge function):
  - Locks the open period.
  - Generates a fair random winner 1–10 (server-side RNG, stored before any payout).
  - For each bet on that period, computes payout (Number 9x, Color: green 2x / violet 4.5x / red 2x, Big/Small 2x) and credits winners' balances.
  - Marks period `settled`, opens the next period.

### 5. Frontend — `/motorace`

- Use the joshgame UI's sprites/PNGs we already copied into `public/moto/assets/png/*` (bikes, podium, track, balls), so visually it looks identical to what your clients saw.
- Pure React (no more iframe). Layout:
  - Top bar: balance, refresh.
  - Current period number + countdown timer (live).
  - Bet area: Green / Violet / Red, numbers 1–10, Big/Small, amount chips (1, 10, 100, 1000), confirm modal.
  - Race animation when period settles (reuse `motoR.json` track + sprite frames).
  - History strip: last 20 winners.
  - My bets tab: pending + recent settled with W/L.

### 6. What we drop from the embed

- The whole joshgame home page, login, wallet, VIP, promotion, activity, chat widget. We're not iframing it anymore for Moto Race.
- The `/moto/*` static files stay only so we can reuse the sprite PNGs as static assets.

## Out of scope this session (called out so there's no confusion)

- WinGo, K3, 5D, Trx games — separate sessions, same pattern.
- Recharge / withdraw / UPI / USDT — balance is purely internal numbers for now; we add real money flow only when you decide on a payment provider.
- Agent commission / referral / VIP tiers — separate feature, not part of any single game.
- Admin panel — comes after at least one game is running so we know the data shape.

## Technical notes

- Settlement runs server-side with `supabaseAdmin`; client never touches result generation.
- `placeBet` uses a Postgres function (SECURITY DEFINER) wrapping balance check + deduction + insert in one transaction to prevent double-spend / race conditions.
- Periods use a deterministic numbering scheme: `MR` + `YYYYMMDD` + 3-digit sequence within the day, reset at 00:00 UTC.
- Random winner uses `gen_random_bytes` on the DB side, not client-controllable.

## After you approve

I'll execute in this order, in one go:

1. Migration (tables + RLS + grants + `place_bet` SQL function + signup trigger).
2. Server functions + edge function for settlement + pg_cron schedule.
3. React `/motorace` page wired to the above.
4. Verify with the browser tool: sign up → see balance → place bet → wait one period → see settlement.