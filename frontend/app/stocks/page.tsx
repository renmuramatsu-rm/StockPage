"use client";

import { Suspense, useEffect, useState } from "react";
import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { StocksIndexData, getStocks } from "@/lib/api";
import ScoreBadge from "@/components/ScoreBadge";
import FavoriteButton from "@/components/FavoriteButton";
import { useCurrentUser } from "@/lib/useAuth";

function StocksContent() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const user = useCurrentUser();
  const [data, setData] = useState<StocksIndexData | null>(null);
  const [error, setError] = useState<string | null>(null);

  const q = searchParams.get("q") ?? "";
  const themeId = searchParams.get("theme_id") ?? "";
  const badge = searchParams.get("badge") ?? "";
  const page = searchParams.get("page") ?? "1";

  useEffect(() => {
    const params: Record<string, string> = { page };
    if (q) params.q = q;
    if (themeId) params.theme_id = themeId;
    if (badge) params.badge = badge;

    getStocks(params)
      .then(setData)
      .catch((e) => setError(e instanceof Error ? e.message : "読み込みに失敗しました。"));
  }, [q, themeId, badge, page]);

  function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const params = new URLSearchParams();
    for (const key of ["q", "theme_id", "badge"]) {
      const value = form.get(key);
      if (value) params.set(key, String(value));
    }
    router.push(`/stocks?${params.toString()}`);
  }

  const hasFilters = q || themeId || badge;

  return (
    <>
      <div className="flex items-center justify-between mb-6 flex-wrap gap-2">
        <h1 className="text-2xl font-bold text-slate-900">銘柄一覧</h1>
        <p className="text-sm text-slate-500 font-mono">{data ? `${data.stocks.total} 件` : ""}</p>
      </div>

      <form
        onSubmit={handleSubmit}
        className="mb-6 bg-white border border-slate-200 rounded-xl shadow-sm p-4 flex flex-wrap gap-4 items-end"
      >
        <div className="flex-1 min-w-[220px]">
          <label className="block text-sm font-medium mb-1 text-slate-700" htmlFor="q">
            銘柄コード・銘柄名で検索
          </label>
          <div className="relative">
            <svg
              className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              strokeWidth={2}
            >
              <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
            </svg>
            <input
              type="text"
              name="q"
              id="q"
              defaultValue={q}
              placeholder="例: 7203 またはトヨタ"
              className="border border-slate-300 rounded-lg pl-9 pr-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
        </div>
        <div>
          <label className="block text-sm font-medium mb-1 text-slate-700" htmlFor="theme_id">
            テーマ
          </label>
          <select
            name="theme_id"
            id="theme_id"
            defaultValue={themeId}
            className="border border-slate-300 rounded-lg px-3 py-2 bg-white"
          >
            <option value="">すべて</option>
            {data?.themes.map((theme) => (
              <option key={theme.id} value={theme.id}>
                {theme.name}
              </option>
            ))}
          </select>
        </div>
        <div>
          <label className="block text-sm font-medium mb-1 text-slate-700" htmlFor="badge">
            投資判断
          </label>
          <select
            name="badge"
            id="badge"
            defaultValue={badge}
            className="border border-slate-300 rounded-lg px-3 py-2 bg-white"
          >
            <option value="">すべて</option>
            {data?.badges.map((b) => (
              <option key={b} value={b}>
                {b}
              </option>
            ))}
            <option value="未評価">未評価（まだ見ていない銘柄）</option>
          </select>
        </div>
        <button type="submit" className="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
          絞り込む
        </button>
        {hasFilters && (
          <Link href="/stocks" className="text-sm text-slate-500 hover:text-indigo-600">
            解除
          </Link>
        )}
      </form>

      {error && <p className="text-red-600 bg-red-50 border border-red-200 rounded-lg p-4 mb-4">{error}</p>}

      <div className="border border-slate-200 rounded-xl shadow-sm bg-white overflow-x-auto">
        <table className="w-full text-sm border-collapse min-w-[720px]">
          <thead>
            <tr className="border-b border-slate-200 bg-slate-50 text-left">
              {user && <th className="py-2.5 px-3 w-10"></th>}
              <th className="py-2.5 px-3 text-slate-500 font-medium">コード</th>
              <th className="py-2.5 px-3 text-slate-500 font-medium">銘柄名</th>
              <th className="py-2.5 px-3 text-slate-500 font-medium">市場</th>
              <th className="py-2.5 px-3 text-slate-500 font-medium">投資判断</th>
              <th className="py-2.5 px-3 text-slate-500 font-medium">テーマ</th>
            </tr>
          </thead>
          <tbody>
            {!data ? (
              <tr>
                <td colSpan={6} className="py-8 px-3 text-center text-slate-400">
                  読み込んでいます…
                </td>
              </tr>
            ) : data.stocks.data.length === 0 ? (
              <tr>
                <td colSpan={6} className="py-8 px-3 text-center text-slate-400">
                  該当する銘柄がありません。
                </td>
              </tr>
            ) : (
              data.stocks.data.map((stock) => (
                <tr
                  key={stock.code}
                  className="border-b border-slate-100 last:border-0 odd:bg-white even:bg-slate-50/60 hover:bg-indigo-50/60 transition-colors"
                >
                  {user && (
                    <td className="py-2.5 px-3">
                      <FavoriteButton code={stock.code} initialFavorited={data.favoriteCodes.includes(stock.code)} size="sm" />
                    </td>
                  )}
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
              ))
            )}
          </tbody>
        </table>
      </div>

      {data && data.stocks.last_page > 1 && (
        <div className="mt-6 flex items-center justify-center gap-2 text-sm">
          <PageLink page={data.stocks.current_page - 1} disabled={data.stocks.current_page <= 1} label="前へ" />
          <span className="text-slate-500 font-mono px-2">
            {data.stocks.current_page} / {data.stocks.last_page}
          </span>
          <PageLink page={data.stocks.current_page + 1} disabled={data.stocks.current_page >= data.stocks.last_page} label="次へ" />
        </div>
      )}
    </>
  );

  function PageLink({ page: p, disabled, label }: { page: number; disabled: boolean; label: string }) {
    if (disabled) {
      return <span className="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-300">{label}</span>;
    }
    const params = new URLSearchParams(searchParams.toString());
    params.set("page", String(p));
    return (
      <Link
        href={`/stocks?${params.toString()}`}
        className="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:border-indigo-300 hover:text-indigo-600 transition"
      >
        {label}
      </Link>
    );
  }
}

export default function StocksIndexPage() {
  return (
    <Suspense fallback={<p className="text-slate-500">読み込んでいます…</p>}>
      <StocksContent />
    </Suspense>
  );
}
