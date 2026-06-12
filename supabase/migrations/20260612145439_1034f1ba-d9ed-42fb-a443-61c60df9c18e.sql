
CREATE EXTENSION IF NOT EXISTS pg_cron;
CREATE EXTENSION IF NOT EXISTS pg_net;

-- Unschedule if previously scheduled (idempotent)
DO $$
BEGIN
  PERFORM cron.unschedule('motorace-settle-every-minute');
EXCEPTION WHEN OTHERS THEN
  -- ignore
END $$;

SELECT cron.schedule(
  'motorace-settle-every-minute',
  '* * * * *',
  $$
  SELECT net.http_post(
    url := 'https://project--9899f4cf-f65d-4bf4-ae33-dd1ffa46f2c0-dev.lovable.app/api/public/v1/motorace/settle',
    headers := '{"Content-Type":"application/json","apikey":"eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imtkc3drbGNwYnpvaXh4d21tZWNzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODEyNjYyOTQsImV4cCI6MjA5Njg0MjI5NH0.cfRIGxiQvyKrdUWoFHtl8QTrOsSMaA29BryynCgS0gs"}'::jsonb,
    body := '{}'::jsonb
  );
  $$
);
