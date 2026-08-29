"use client";

import { useState } from "react";
import { addFavorite, removeFavorite } from "@/lib/api";

export default function FavoriteButton({
  code,
  initialFavorited,
  size = "md",
  onToggle,
}: {
  code: string;
  initialFavorited: boolean;
  size?: "sm" | "md";
  onToggle?: (favorited: boolean) => void;
}) {
  const [favorited, setFavorited] = useState(initialFavorited);
  const [busy, setBusy] = useState(false);

  async function toggle(event: React.MouseEvent) {
    event.preventDefault();
    event.stopPropagation();
    if (busy) return;

    setBusy(true);
    const next = !favorited;
    setFavorited(next);
    try {
      if (next) {
        await addFavorite(code);
      } else {
        await removeFavorite(code);
      }
      onToggle?.(next);
    } catch {
      setFavorited(!next);
    } finally {
      setBusy(false);
    }
  }

  const dim = size === "sm" ? "w-4 h-4" : "w-5 h-5";

  return (
    <button
      type="button"
      onClick={toggle}
      disabled={busy}
      aria-pressed={favorited}
      aria-label={favorited ? "お気に入りから外す" : "お気に入りに追加"}
      title={favorited ? "お気に入りから外す" : "お気に入りに追加"}
      className="inline-flex items-center justify-center disabled:opacity-50"
    >
      <svg
        className={`${dim} transition-colors ${favorited ? "fill-amber-400 text-amber-500" : "fill-none text-slate-300 hover:text-amber-400"}`}
        viewBox="0 0 24 24"
        stroke="currentColor"
        strokeWidth={1.5}
      >
        <path
          strokeLinecap="round"
          strokeLinejoin="round"
          d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385c.117.499-.397.916-.836.685l-4.725-2.885a.563.563 0 0 0-.586 0L6.982 21.6c-.439.239-.953-.178-.836-.685l1.285-5.385a.562.562 0 0 0-.182-.557l-4.204-3.602c-.38-.325-.178-.948.321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5z"
        />
      </svg>
    </button>
  );
}
