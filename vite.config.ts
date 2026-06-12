import { defineConfig } from "vite";
import { tanstackStart } from "@tanstack/react-start/plugin/vite";
import react from "@vitejs/plugin-react";
import tailwindcss from "@tailwindcss/vite";
import path from "node:path";

export default defineConfig({
  plugins: [tailwindcss(), tanstackStart({ target: "cloudflare-module" }), react()],
  resolve: {
    alias: { "@": path.resolve(__dirname, "./src") },
  },
});
