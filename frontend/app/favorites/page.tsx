"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { Stock, getFavorites } from "@/lib/api";
import { useRequireAuth } from "@/lib/useAuth";
import ScoreBadge from "@/components/ScoreBadge";
import FavoriteButton from "@/components/FavoriteButton";

export default function FavoritesPage() {
  const user = useRequireAuth();
  const [stocks, setStocks] = useState<Stock[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!user) return;
    getFavorites()
      .then((data) => setStocks(data.stocks))
      .catch((e) => setError(e instanceof Error ? e.message : "読み込みに失敗しました。"));
  }, [user]);

  if (!user) return null;
  if (error) return <p className="text-red-600 bg-red-50 border border-red-200 rounded-lg p-4">{error}</p>;
  if (!stocks) return <p className="text-slate-500">読み込んでいます…</p>;

  return (
    <>
      <div className="flex items-center justify-between mb-6 flex-wrap gap-2">
        <h1 className="text-2xl font-bold text-slate-900">お気に入り</h1>
        <p className="text-sm text-slate-500 font-mono">{stocks.length} 件</p>
      </div>

      {stocks.length === 0 ? (
        <p className="text-slate-500">
          お気に入りに追加した銘柄はまだありません。
          <Link href="/stocks" className="text-indigo-600 hover:underline">
            銘柄一覧
          </Link>
          や銘柄詳細ページの星マークから追加できます。
        </p>
      ) : (
        <div className="border border-slate-200 rounded-xl shadow-sm bg-white overflow-x-auto">
          <table className="w-full text-sm border-collapse min-w-[720px]">
            <thead>
              <tr className="border-b border-slate-200 bg-slate-50 text-left">
                <th className="py-2.5 px-3 w-10"></th>
                <th className="py-2.5 px-3 text-slate-500 font-medium">コード</th>
                <th className="py-2.5 px-3 text-slate-500 font-medium">銘柄名</th>
                <th className="py-2.5 px-3 text-slate-500 font-medium">市場</th>
                <th className="py-2.5 px-3 text-slate-500 font-medium">投資判断</th>
                <th className="py-2.5 px-3 text-slate-500 font-medium">テーマ</th>
              </tr>
            </thead>
            <tbody>
              {stocks.map((stock) => (
                <tr
                  key={stock.code}
                  className="border-b border-slate-100 last:border-0 odd:bg-white even:bg-slate-50/60 hover:bg-indigo-50/60 transition-colors"
                >
                  <td className="py-2.5 px-3">
                    <FavoriteButton
                      code={stock.code}
                      initialFavorited
                      size="sm"
                      onToggle={(favorited) => {
                        if (!favorited) {
                          setStocks((prev) => prev?.filter((s) => s.code !== stock.code) ?? null);
                        }
                      }}
                    />
                  </td>
                  <td className="py-2.5 px-3 font-mono">
                    <Link className="text-indigo-600 hover:underline font-medium" href={`/stocks/${stock.code}`}>
                      {stock.code}
                    </Link>
                  </td>
                  <td className="py-2.5 px-3">
                    <Link className="hover:underline hover:text-indigo-700" href={`/stocks/${stock.code}`}>
                      {stock.stockName}
                    </Link>
                  </td>
                  <td className="py-2.5 px-3 text-slate-500">{stock.market?.market}</td>
                  <td className="py-2.5 px-3">
                    <ScoreBadge score={stock.score} />
                  </td>
                  <td className="py-2.5 px-3">
                    {stock.themes?.map((theme) => (
                      <span
                        key={theme.id}
                        className="inline-block bg-slate-100 text-slate-600 rounded-full px-2 py-0.5 text-xs mr-1 whitespace-nowrap"
                      >
                        {theme.name}
                      </span>
                    ))}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </>
  );
}
