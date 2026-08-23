"use client";

import { useState } from "react";
import { Theme, ThemeInput } from "@/lib/api";

export default function ThemeForm({
  theme,
  onSubmit,
  submitLabel = "保存",
}: {
  theme?: Theme;
  onSubmit: (data: ThemeInput) => Promise<void>;
  submitLabel?: string;
}) {
  const [name, setName] = useState(theme?.name ?? "");
  const [description, setDescription] = useState(theme?.description ?? "");
  const [color, setColor] = useState(theme?.color ?? "");
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function handleSubmit(event: React.FormEvent) {
    event.preventDefault();
    setError(null);
    setSubmitting(true);
    try {
      await onSubmit({ name, description, color });
    } catch (err) {
      setError(err instanceof Error ? err.message : "保存に失敗しました。");
      setSubmitting(false);
    }
  }

  return (
    <form onSubmit={handleSubmit} className="max-w-lg bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
      {error && (
        <p className="text-red-600 text-sm mb-4 bg-red-50 border border-red-100 rounded-lg px-3 py-2">{error}</p>
      )}

      <div className="mb-4">
        <label className="block text-sm font-medium mb-1 text-slate-700" htmlFor="name">
          テーマ名
        </label>
        <input
          className="border border-slate-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          type="text"
          id="name"
          value={name}
          onChange={(e) => setName(e.target.value)}
          required
        />
      </div>

      <div className="mb-4">
        <label className="block text-sm font-medium mb-1 text-slate-700" htmlFor="description">
          説明
        </label>
        <textarea
          className="border border-slate-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          id="description"
          rows={3}
          value={description}
          onChange={(e) => setDescription(e.target.value)}
        />
      </div>

      <div className="mb-6">
        <label className="block text-sm font-medium mb-1 text-slate-700" htmlFor="color">
          カラー（任意、例: #2563eb）
        </label>
        <input
          className="border border-slate-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          type="text"
          id="color"
          value={color}
          onChange={(e) => setColor(e.target.value)}
        />
      </div>

      <button
        type="submit"
        disabled={submitting}
        className="bg-indigo-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-indigo-700 transition disabled:opacity-60"
      >
        {submitting ? "保存中…" : submitLabel}
      </button>
    </form>
  );
}
