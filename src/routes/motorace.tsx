import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/motorace")({
  component: MotoracePage,
  head: () => ({
    meta: [{ title: "Moto Race" }],
  }),
});

function MotoracePage() {
  return (
    <div className="fixed inset-0 bg-black">
      <iframe
        src="/moto/index.html#/AllLotteryGames-MotoRace?gameCode=MotoRace_1M"
        title="Moto Race"
        className="h-full w-full border-0"
        allow="autoplay; fullscreen; clipboard-read; clipboard-write"
      />
    </div>
  );
}
