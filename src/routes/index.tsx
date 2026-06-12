import { createFileRoute, Link } from "@tanstack/react-router";

export const Route = createFileRoute("/")({
  component: Landing,
});

function Landing() {
  return (
    <main className="mx-auto flex min-h-screen max-w-3xl flex-col items-start justify-center gap-6 px-6">
      <span className="rounded-full border border-border bg-surface px-3 py-1 text-xs uppercase tracking-wider text-muted">
        Phase 1 — Foundation
      </span>
      <h1 className="text-4xl font-semibold leading-tight">
        Multi-tenant game provider platform
      </h1>
      <p className="max-w-xl text-muted">
        Seamless wallet API, per-operator API keys with IP/domain whitelisting,
        and a central admin to manage operators and games.
      </p>
      <div className="flex flex-wrap gap-3">
        <Link
          to="/auth"
          className="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-fg hover:opacity-90"
        >
          Admin sign in
        </Link>
        <Link
          to="/motorace"
          className="rounded-md border border-border bg-surface px-4 py-2 text-sm font-medium hover:opacity-90"
        >
          Play Moto Race →
        </Link>
      </div>
      <ul className="mt-6 grid grid-cols-1 gap-2 text-sm text-muted sm:grid-cols-2">
        <li className="rounded-md border border-border bg-surface p-3">✓ Lovable Cloud enabled</li>
        <li className="rounded-md border border-border bg-surface p-3">✓ Multi-tenant schema migrated</li>
        <li className="rounded-md border border-border bg-surface p-3">✓ 5 games seeded (Moto Race, WinGo, K3, 5D, TRX WinGo)</li>
        <li className="rounded-md border border-border bg-surface p-3">⏳ Admin auth + Operators UI (next)</li>
      </ul>
    </main>
  );
}
