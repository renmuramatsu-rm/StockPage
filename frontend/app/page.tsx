"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { DashboardData, getDashboard } from "@/lib/api";
import { formatNumber, relativeTimeJa } from "@/lib/format";
import { useRequireAuth } from "@/lib/useAuth";

export default function DashboardPage() {
  const user = useRequireAuth();
  const [data, setData] = useState<DashboardData | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!user) return;
    getDashboard()
      .then(setData)
      .catch((e) => setError(e instanceof Error ? e.message : "読み込みに失敗しました。"));
  }, [user]);

  if (!user) return null;

  if (error) {
    return <p className="text-red-600 bg-red-50 border border-red-200 rounded-lg p-4">{error}</p>;
  }

  if (!data) {
    return <p className="text-slate-500">読み込んでいます…</p>;
  }

  const { themes, nikkei, stats } = data;

  return (
    <>
      <div className="rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white p-6 sm:p-8 mb-8 shadow-xl shadow-slate-900/20">
        <div className="flex items-start justify-between flex-wrap gap-4">
          <div>
            <h1 className="text-2xl sm:text-3xl font-bold tracking-tight">テーマ別ダッシュボード</h1>
            <p className="text-slate-400 text-sm mt-1">業種テーマは自動更新、独自テーマは手動で追加できます</p>
          </div>
          <div className="bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-sm">
            <span className="text-slate-400">日経平均</span>
            <span className="ml-2 font-mono font-semibold text-amber-300">{nikkei}</span>
          </div>
        </div>

        <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-6">
          <div className="bg-white/5 border border-white/10 rounded-xl p-4">
            <div className="text-slate-400 text-xs">追跡銘柄数</div>
            <div className="text-2xl font-bold font-mono mt-1">{formatNumber(stats.stocks)}</div>
          </div>
          <div className="bg-white/5 border border-white/10 rounded-xl p-4">
            <div className="text-slate-400 text-xs">評価済み銘柄</div>
            <div className="text-2xl font-bold font-mono mt-1">{formatNumber(stats.scored)}</div>
          </div>
          <div className="bg-white/5 border border-white/10 rounded-xl p-4">
            <div className="text-slate-400 text-xs">買い候補</div>
            <div className="text-2xl font-bold font-mono mt-1 text-emerald-400">{formatNumber(stats.buy_candidates)}</div>
          </div>
          <div className="bg-white/5 border border-white/10 rounded-xl p-4">
            <div className="text-slate-400 text-xs">最終更新</div>
            <div className="text-sm font-medium mt-2">{relativeTimeJa(stats.last_synced)}</div>
          </div>
        </div>
      </div>

      <div className="mb-4 flex justify-between items-center flex-wrap gap-2">
        <p className="text-sm text-slate-500">{themes.length} テーマ</p>
        <Link href="/themes/new" className="text-sm font-medium text-indigo-600 hover:text-indigo-800">
          + 新しいテーマを作成
        </Link>
      </div>

      {themes.length === 0 && (
        <p className="text-slate-500">
          テーマがまだありません。
          <code className="bg-slate-200 px-1.5 py-0.5 rounded text-xs">php artisan stocks:sync</code>{" "}
          を実行すると業種テーマが自動で作成されます。
        </p>
      )}

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {themes.map((theme) => (
          <Link
            key={theme.id}
            href={`/themes/${theme.id}`}
            className="group block border border-slate-200 rounded-xl shadow-sm bg-white p-4 hover:shadow-lg hover:shadow-slate-200 hover:-translate-y-0.5 hover:border-indigo-200 transition-all duration-150"
          >
            <div className="flex items-center gap-2 mb-2">
              <span
                className="inline-block w-2.5 h-2.5 rounded-full"
                style={{ backgroundColor: theme.color ?? "#94a3b8" }}
              />
              <h2 className="font-semibold text-slate-800 group-hover:text-indigo-700">{theme.name}</h2>
              {theme.source !== "manual" && (
                <span className="text-[10px] text-slate-400 bg-slate-100 rounded px-1.5 py-0.5 ml-auto">業種</span>
              )}
            </div>
            {theme.description && <p className="text-sm text-slate-500 mb-2">{theme.description}</p>}
            <div className="text-sm text-slate-500 font-mono">{theme.stocks_count ?? 0} 銘柄</div>
          </Link>
        ))}
      </div>
    </>
  );
}
