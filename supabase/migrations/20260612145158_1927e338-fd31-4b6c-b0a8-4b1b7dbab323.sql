
-- =========================================================
-- PLAYER SESSIONS (operator-issued)
-- =========================================================
CREATE TABLE public.player_sessions (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  operator_id uuid NOT NULL REFERENCES public.operators(id) ON DELETE CASCADE,
  external_user_id text NOT NULL,
  display_name text,
  currency text NOT NULL DEFAULT 'INR',
  balance numeric(18,2) NOT NULL DEFAULT 0 CHECK (balance >= 0),
  token_hash text NOT NULL UNIQUE,
  expires_at timestamptz NOT NULL,
  last_active_at timestamptz NOT NULL DEFAULT now(),
  created_at timestamptz NOT NULL DEFAULT now()
);
CREATE INDEX idx_player_sessions_operator_user ON public.player_sessions(operator_id, external_user_id);

GRANT ALL ON public.player_sessions TO service_role;
GRANT SELECT ON public.player_sessions TO authenticated;
ALTER TABLE public.player_sessions ENABLE ROW LEVEL SECURITY;
CREATE POLICY "Admins read player sessions" ON public.player_sessions
  FOR SELECT TO authenticated USING (is_admin(auth.uid()));

-- =========================================================
-- MOTORACE PERIODS
-- =========================================================
CREATE TABLE public.motorace_periods (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  period_no text NOT NULL UNIQUE,
  starts_at timestamptz NOT NULL,
  ends_at timestamptz NOT NULL,
  status text NOT NULL DEFAULT 'open' CHECK (status IN ('open','locked','settled')),
  result_winner int CHECK (result_winner BETWEEN 1 AND 10),
  settled_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT now()
);
CREATE INDEX idx_motorace_periods_status ON public.motorace_periods(status);
CREATE INDEX idx_motorace_periods_ends_at ON public.motorace_periods(ends_at DESC);

GRANT ALL ON public.motorace_periods TO service_role;
GRANT SELECT ON public.motorace_periods TO authenticated;
ALTER TABLE public.motorace_periods ENABLE ROW LEVEL SECURITY;
CREATE POLICY "Admins read periods" ON public.motorace_periods
  FOR SELECT TO authenticated USING (is_admin(auth.uid()));

-- =========================================================
-- MOTORACE BETS
-- =========================================================
CREATE TABLE public.motorace_bets (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  session_id uuid NOT NULL REFERENCES public.player_sessions(id) ON DELETE CASCADE,
  period_id uuid NOT NULL REFERENCES public.motorace_periods(id) ON DELETE CASCADE,
  bet_type text NOT NULL CHECK (bet_type IN ('number','color','bigsmall')),
  bet_value text NOT NULL,
  amount numeric(18,2) NOT NULL CHECK (amount > 0),
  payout numeric(18,2),
  status text NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','won','lost')),
  created_at timestamptz NOT NULL DEFAULT now(),
  settled_at timestamptz
);
CREATE INDEX idx_motorace_bets_session ON public.motorace_bets(session_id, created_at DESC);
CREATE INDEX idx_motorace_bets_period ON public.motorace_bets(period_id);
CREATE INDEX idx_motorace_bets_pending ON public.motorace_bets(period_id) WHERE status = 'pending';

GRANT ALL ON public.motorace_bets TO service_role;
GRANT SELECT ON public.motorace_bets TO authenticated;
ALTER TABLE public.motorace_bets ENABLE ROW LEVEL SECURITY;
CREATE POLICY "Admins read bets" ON public.motorace_bets
  FOR SELECT TO authenticated USING (is_admin(auth.uid()));

-- =========================================================
-- HELPER: generate next period number for today
-- Format: MR + YYYYMMDD + 4-digit sequence
-- =========================================================
CREATE OR REPLACE FUNCTION public.next_motorace_period_no()
RETURNS text
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
DECLARE
  prefix text := 'MR' || to_char(now() AT TIME ZONE 'UTC', 'YYYYMMDD');
  seq int;
BEGIN
  SELECT COALESCE(MAX( (substr(period_no, 11))::int ), 0) + 1
    INTO seq
    FROM public.motorace_periods
   WHERE period_no LIKE prefix || '%';
  RETURN prefix || lpad(seq::text, 4, '0');
END $$;

-- =========================================================
-- HELPER: place a bet atomically
-- Validates token, locks session row, checks period is open,
-- deducts balance, inserts bet. Returns new balance + bet id.
-- =========================================================
CREATE OR REPLACE FUNCTION public.place_motorace_bet(
  _token_hash text,
  _period_id uuid,
  _bet_type text,
  _bet_value text,
  _amount numeric
)
RETURNS TABLE (bet_id uuid, new_balance numeric)
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
DECLARE
  v_session_id uuid;
  v_balance numeric;
  v_status text;
  v_ends_at timestamptz;
  v_bet_id uuid;
BEGIN
  IF _amount <= 0 THEN RAISE EXCEPTION 'invalid_amount'; END IF;
  IF _bet_type NOT IN ('number','color','bigsmall') THEN RAISE EXCEPTION 'invalid_bet_type'; END IF;

  SELECT id, balance INTO v_session_id, v_balance
    FROM public.player_sessions
   WHERE token_hash = _token_hash AND expires_at > now()
   FOR UPDATE;
  IF v_session_id IS NULL THEN RAISE EXCEPTION 'invalid_session'; END IF;
  IF v_balance < _amount THEN RAISE EXCEPTION 'insufficient_balance'; END IF;

  SELECT status, ends_at INTO v_status, v_ends_at
    FROM public.motorace_periods WHERE id = _period_id;
  IF v_status IS NULL OR v_status <> 'open' OR v_ends_at <= now() + interval '3 seconds' THEN
    RAISE EXCEPTION 'period_closed';
  END IF;

  UPDATE public.player_sessions
     SET balance = balance - _amount, last_active_at = now()
   WHERE id = v_session_id;

  INSERT INTO public.motorace_bets(session_id, period_id, bet_type, bet_value, amount)
  VALUES (v_session_id, _period_id, _bet_type, _bet_value, _amount)
  RETURNING id INTO v_bet_id;

  RETURN QUERY SELECT v_bet_id, v_balance - _amount;
END $$;

GRANT EXECUTE ON FUNCTION public.place_motorace_bet(text,uuid,text,text,numeric) TO service_role;

-- Seed MotoRace into games catalog so admin UI can list it later.
INSERT INTO public.games(code, name, category, enabled)
VALUES ('motorace', 'Moto Race', 'lottery', true)
ON CONFLICT (code) DO NOTHING;
