import { createFileRoute, Link, Outlet, useNavigate, useRouterState } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { supabase } from "@/integrations/supabase/client";

export const Route = createFileRoute("/_authenticated")({
  component: AuthedLayout,
});

function AuthedLayout() {
  const navigate = useNavigate();
  const [status, setStatus] = useState<"loading" | "ok" | "denied">("loading");
  const pathname = useRouterState({ select: (s) => s.location.pathname });

  useEffect(() => {
    let cancelled = false;
    (async () => {
      const { data } = await supabase.auth.getSession();
      if (!data.session) {
        navigate({ to: "/auth" });
        return;
      }
      const { data: roles } = await supabase
        .from("user_roles")
        .select("role")
        .eq("user_id", data.session.user.id);
      if (cancelled) return;
      const isAdmin = (roles ?? []).some((r) => r.role === "admin" || r.role === "super_admin");
      setStatus(isAdmin ? "ok" : "denied");
    })();
    return () => {
      cancelled = true;
    };
  }, [navigate]);

  if (status === "loading") {
    return <div className="flex min-h-screen items-center justify-center text-muted">Loading…</div>;
  }
  if (status === "denied") {
    return (
      <div className="flex min-h-screen flex-col items-center justify-center gap-3 text-center">
        <p>Your account has no admin role.</p>
        <button
          onClick={async () => {
            await supabase.auth.signOut();
            navigate({ to: "/auth" });
          }}
          className="rounded-md border border-border px-3 py-1.5 text-sm"
        >
          Sign out
        </button>
      </div>
    );
  }

  const nav = [
    { to: "/dashboard", label: "Dashboard" },
    { to: "/operators", label: "Operators" },
    { to: "/games", label: "Games" },
  ] as const;

  return (
    <div className="flex min-h-screen">
      <aside className="flex w-56 shrink-0 flex-col gap-1 border-r border-border bg-surface p-4">
        <div className="mb-4 text-sm font-semibold">Game SaaS · Admin</div>
        {nav.map((n) => {
          const active = pathname === n.to || pathname.startsWith(n.to + "/");
          return (
            <Link
              key={n.to}
              to={n.to}
              className={`rounded-md px-3 py-2 text-sm ${active ? "bg-primary text-primary-fg" : "hover:bg-border/40"}`}
            >
              {n.label}
            </Link>
          );
        })}
        <button
          onClick={async () => {
            await supabase.auth.signOut();
            navigate({ to: "/auth" });
          }}
          className="mt-auto rounded-md border border-border px-3 py-2 text-xs text-muted hover:bg-border/40"
        >
          Sign out
        </button>
      </aside>
      <main className="flex-1 p-8">
        <Outlet />
      </main>
    </div>
  );
}
