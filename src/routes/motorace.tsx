import { createFileRoute, useSearch } from "@tanstack/react-router";
import { useEffect, useState } from "react";

export const Route = createFileRoute("/motorace")({
  validateSearch: (s: Record<string, unknown>) => ({ token: (s.token as string) || "" }),
  component: MotoracePage,
  head: () => ({ meta: [{ title: "MotoRace 1Min" }] }),
});

function MotoracePage() {
  const { token } = useSearch({ from: "/motorace" });
  const [status, setStatus] = useState("Loading demo session…");

  useEffect(() => {
    if (token) {
      setStatus("");
      return;
    }
    fetch("/api/public/v1/motorace/demo")
      .then((r) => r.json())
      .then((j) => {
        if (j.token) window.location.href = `/motorace?token=${j.token}`;
      })
      .catch(() => setStatus("Demo could not start"));
  }, [token]);

  return (
    <main className="min-h-screen w-full" style={{ background: "#000" }}>
      <div className="mx-auto" style={{ maxWidth: 420 }}>
        {status ? (
          <div className="flex h-[100dvh] items-center justify-center text-white">{status}</div>
        ) : (
          <iframe
            src="/moto/index.html"
            title="MotoRace"
            className="h-[100dvh] w-full border-0"
            allow="autoplay; fullscreen"
          />
        )}
      </div>
    </main>
  );
}
