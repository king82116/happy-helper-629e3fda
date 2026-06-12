import { createRootRoute, HeadContent, Outlet, Scripts } from "@tanstack/react-router";
import styles from "../styles.css?url";

export const Route = createRootRoute({
  head: () => ({
    meta: [
      { charSet: "utf-8" },
      { name: "viewport", content: "width=device-width, initial-scale=1" },
      { title: "Game SaaS — Admin" },
    ],
    links: [{ rel: "stylesheet", href: styles }],
  }),
  shellComponent: RootShell,
  notFoundComponent: () => (
    <div className="flex min-h-screen items-center justify-center text-muted">404 — not found</div>
  ),
});

function RootShell() {
  return (
    <html lang="en">
      <head>
        <HeadContent />
      </head>
      <body>
        <Outlet />
        <Scripts />
      </body>
    </html>
  );
}
