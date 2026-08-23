"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useParams, useRouter } from "next/navigation";
import { Stock, Theme, getStockThemes, updateStockThemes } from "@/lib/api";
import { useRequireAuth } from "@/lib/useAuth";

export default function StockThemesPage() {
  const user = useRequireAuth();
  const router = useRouter();
  const { code } = useParams<{ code: string }>();
  const [stock, setStock] = useState<Stock | null>(null);
  const [themes, setThemes] = useState<Theme[]>([]);
  const [selected, setSelected] = useState<Set<number>>(new Set());
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (!user) return;
    getStockThemes(code)
      .then((data) => {
        setStock(data.stock);
        setThemes(data.themes);
        setSelected(new Set((data.stock.themes ?? []).map((t) => t.id)));
      })
      .catch((e) => setError(e instanceof Error ? e.message : "読み込みに失敗しました。"));
  }, [user, code]);

  if (!user) return null;
  if (error) return <p className="text-red-600 bg-red-50 border border-red-200 rounded-lg p-4">{error}</p>;
  if (!stock) return <p className="text-slate-500">読み込んでいます…</p>;

  function toggle(id: number) {
    setSelected((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  }

  async function handleSubmit(event: React.FormEvent) {
    event.preventDefault();
    setSubmitting(true);
    try {
      await updateStockThemes(code, Array.from(selected));
      router.push(`/stocks/${code}`);
    } catch (e) {
      setError(e instanceof Error ? e.message : "保存に失敗しました。");
      setSubmitting(false);
    }
  }

  return (
    <>
      <div className="mb-3">
        <Link href={`/stocks/${code}`} className="text-sm text-slate-500 hover:text-indigo-600">
          &larr; {stock.stockName}へ
        </Link>
      </div>
      <h1 className="text-2xl font-bold mb-6 text-slate-900">
        {stock.stockName} <span className="text-slate-400 font-mono text-lg">{stock.code}</span> のテーマ
      </h1>

      <form onSubmit={handleSubmit} className="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 max-w-lg">
        <div className="mb-6 flex flex-col gap-1 max-h-96 overflow-y-auto">
          {themes.length === 0 ? (
            <p className="text-slate-500">
              テーマがまだありません。
              <Link className="text-indigo-600 hover:underline" href="/themes/new">
                先にテーマを作成
              </Link>
              してください。
            </p>
          ) : (
            themes.map((theme) => (
              <label key={theme.id} className="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 cursor-pointer">
                <input
                  type="checkbox"
                  checked={selected.has(theme.id)}
                  onChange={() => toggle(theme.id)}
                  className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                />
                <span className="text-slate-700">{theme.name}</span>
              </label>
            ))
          )}
        </div>

        <button
          type="submit"
          disabled={submitting}
          className="bg-indigo-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-indigo-700 transition disabled:opacity-60"
        >
          {submitting ? "保存中…" : "保存"}
        </button>
      </form>
    </>
  );
}
