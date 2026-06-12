
REVOKE EXECUTE ON FUNCTION public.next_motorace_period_no() FROM PUBLIC, anon, authenticated;
REVOKE EXECUTE ON FUNCTION public.place_motorace_bet(text,uuid,text,text,numeric) FROM PUBLIC, anon, authenticated;
GRANT EXECUTE ON FUNCTION public.next_motorace_period_no() TO service_role;
GRANT EXECUTE ON FUNCTION public.place_motorace_bet(text,uuid,text,text,numeric) TO service_role;
