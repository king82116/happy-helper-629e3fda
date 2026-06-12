import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { supabase } from "@/integrations/supabase/client";

export const Route = createFileRoute("/auth")({
  component: AuthPage,
});

function AuthPage() {
  const navigate = useNavigate();
  const [mode, setMode] = useState<"signin" | "signup">("signin");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    supabase.auth.getSession().then(({ data }) => {
      if (data.session) navigate({ to: "/dashboard" });
    });
  }, [navigate]);

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setLoading(true);
    setError(null);
    try {
      const fn =
        mode === "signin"
          ? supabase.auth.signInWithPassword({ email, password })
          : supabase.auth.signUp({
              email,
              password,
              options: { emailRedirectTo: `${window.location.origin}/dashboard` },
            });
      const { error: err } = await fn;
      if (err) throw err;
      navigate({ to: "/dashboard" });
    } catch (err: any) {
      setError(err.message ?? "Failed");
    } finally {
      setLoading(false);
    }
  }

  return (
    <main className="mx-auto flex min-h-screen max-w-md flex-col justify-center gap-6 px-6">
      <header className="space-y-1">
        <h1 className="text-2xl font-semibold">Admin {mode === "signin" ? "sign in" : "sign up"}</h1>
        <p className="text-sm text-muted">
          {mode === "signup"
            ? "First account becomes super-admin automatically."
            : "Sign in to manage operators and games."}
        </p>
      </header>
      <form onSubmit={onSubmit} className="flex flex-col gap-3">
        <input
          type="email"
          required
          placeholder="email@example.com"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          className="rounded-md border border-border bg-surface px-3 py-2 text-sm"
        />
        <input
          type="password"
          required
          minLength={6}
          placeholder="password (min 6 chars)"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          className="rounded-md border border-border bg-surface px-3 py-2 text-sm"
        />
        {error && <p className="text-sm text-red-500">{error}</p>}
        <button
          type="submit"
          disabled={loading}
          className="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-fg disabled:opacity-50"
        >
          {loading ? "..." : mode === "signin" ? "Sign in" : "Create account"}
        </button>
      </form>
      <button
        onClick={() => setMode(mode === "signin" ? "signup" : "signin")}
        className="text-xs text-muted underline-offset-4 hover:underline"
      >
        {mode === "signin" ? "Need an account? Sign up" : "Have an account? Sign in"}
      </button>
    </main>
  );
}
