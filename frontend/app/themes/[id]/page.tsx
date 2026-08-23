"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useParams } from "next/navigation";
import { ThemeShowData, getTheme } from "@/lib/api";
import { useRequireAuth } from "@/lib/useAuth";
import TrendChart from "@/components/TrendChart";
import ScoreBadge from "@/components/ScoreBadge";

export default function ThemeShowPage() {
  const user = useRequireAuth();
  const { id } = useParams<{ id: string }>();
  const [data, setData] = useState<ThemeShowData | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!user) return;
    getTheme(id)
      .then(setData)
      .catch((e) => setError(e instanceof Error ? e.message : "読み込みに失敗しました。"));
  }, [user, id]);

  if (!user) return null;
  if (error) return <p className="text-red-600 bg-red-50 border border-red-200 rounded-lg p-4">{error}</p>;
  if (!data) return <p className="text-slate-500">読み込んでいます…</p>;

  const { theme, chartConfig, truncated } = data;

  return (
    <>
      <div className="mb-3">
        <Link href="/" className="text-sm text-slate-500 hover:text-indigo-600">
          &larr; ダッシュボードへ
        </Link>
      </div>

      <div className="mb-6 flex items-start justify-between flex-wrap gap-2">
        <div>
          <div className="flex items-center gap-2">
            <span className="inline-block w-3 h-3 rounded-full" style={{ backgroundColor: theme.color ?? "#94a3b8" }} />
            <h1 className="text-2xl font-bold text-slate-900">{theme.name}</h1>
          </div>
          {theme.description && <p className="text-slate-500 text-sm mt-1">{theme.description}</p>}
          <p className="text-sm text-slate-400 mt-1 font-mono">{theme.stocks.length} 銘柄</p>
        </div>
        {theme.user_id !== null && (
          <Link href={`/themes/${theme.id}/edit`} className="text-sm font-medium text-indigo-600 hover:text-indigo-800">
            編集
          </Link>
        )}
      </div>

      <div className="border border-slate-200 rounded-2xl shadow-sm bg-white p-4 mb-8">
        <TrendChart config={chartConfig} />
        {truncated && (
          <p className="text-xs text-slate-400 mt-2">
            ※銘柄数が多いため、グラフは売上高上位の銘柄のみ表示しています。下の一覧には全銘柄を掲載しています。
          </p>
        )}
      </div>

      <div className="border border-slate-200 rounded-2xl shadow-sm bg-white overflow-x-auto">
        <table className="w-full text-sm border-collapse min-w-[560px]">
          <thead>
            <tr className="border-b border-slate-200 bg-slate-50 text-left">
              <th className="py-2.5 px-3 text-slate-500 font-medium">銘柄コード</th>
              <th className="py-2.5 px-3 text-slate-500 font-medium">銘柄名</th>
              <th className="py-2.5 px-3 text-slate-500 font-medium">市場</th>
              <th className="py-2.5 px-3 text-slate-500 font-medium">投資判断</th>
            </tr>
          </thead>
          <tbody>
            {theme.stocks.map((stock) => (
              <tr
                key={stock.code}
                className="border-b border-slate-100 last:border-0 odd:bg-white even:bg-slate-50/60 hover:bg-indigo-50/60 transition-colors"
              >
                <td className="py-2 px-3 font-mono">
                  <Link className="text-indigo-600 hover:underline font-medium" href={`/stocks/${stock.code}`}>
                    {stock.code}
                  </Link>
                </td>
                <td className="py-2 px-3">
                  <Link className="hover:underline hover:text-indigo-700" href={`/stocks/${stock.code}`}>
                    {stock.stockName}
                  </Link>
                </td>
                <td className="py-2 px-3 text-slate-500">{stock.market?.market}</td>
                <td className="py-2 px-3">
                  <ScoreBadge score={stock.score} />
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </>
  );
}
