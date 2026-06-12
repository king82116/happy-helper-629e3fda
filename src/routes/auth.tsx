import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/auth")({
  component: () => (
    <div className="mx-auto flex min-h-screen max-w-md flex-col items-center justify-center gap-3 px-6 text-center">
      <h1 className="text-2xl font-semibold">Admin sign in</h1>
      <p className="text-sm text-muted">Coming in the next step.</p>
    </div>
  ),
});
