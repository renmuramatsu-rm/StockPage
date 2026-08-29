"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { SbiHoldingsData, getSbiHoldings } from "@/lib/api";
import { formatNumber, relativeTimeJa } from "@/lib/format";
import { useRequireAuth } from "@/lib/useAuth";
import TrendChart from "@/components/TrendChart";
import ScoreBadge from "@/components/ScoreBadge";

export default function SbiHoldingsPage() {
  const user = useRequireAuth();
  const [data, setData] = useState<SbiHoldingsData | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!user) return;
    getSbiHoldings()
      .then(setData)
      .catch((e) => setError(e instanceof Error ? e.message : "読み込みに失敗しました。"));
  }, [user]);

  if (!user) return null;
  if (error) return <p className="text-red-600 bg-red-50 border border-red-200 rounded-lg p-4">{error}</p>;
  if (!data) return <p className="text-slate-500">読み込んでいます…</p>;

  const { holdings, allocationChartConfig, summary, portfolio } = data;

  return (
    <>
      <div className="mb-6 flex items-center justify-between">
        <h1 className="text-2xl font-bold text-slate-900">SBI保有銘柄</h1>
        <Link href="/sbi-holdings/new" className="text-sm font-medium text-indigo-600 hover:text-indigo-800">
          + 保有銘柄を追加
        </Link>
      </div>

      {holdings.length === 0 ? (
        <p className="text-slate-500">保有銘柄が登録されていません。SBI証券の保有銘柄画面を見ながら、銘柄コード・株数・取得単価を入力してください。</p>
      ) : (
        <>
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div className="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">
              <div className="text-xs text-slate-500">取得金額合計</div>
              <div className="text-xl font-bold font-mono mt-1 text-slate-900">{formatNumber(summary.cost)}円</div>
            </div>
            <div className="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">
              <div className="text-xs text-slate-500">評価額合計</div>
              <div className="text-xl font-bold font-mono mt-1 text-slate-900">
                {summary.value !== null ? `${formatNumber(summary.value)}円` : "—"}
              </div>
            </div>
            <div className="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">
              <div className="text-xs text-slate-500">評価損益合計</div>
              <div className={`text-xl font-bold font-mono mt-1 ${(summary.pl ?? 0) < 0 ? "text-emerald-600" : "text-rose-600"}`}>
                {summary.pl !== null ? `${summary.pl > 0 ? "+" : ""}${formatNumber(summary.pl)}円` : "—"}
              </div>
            </div>
          </div>

          <div className="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 mb-8">
            <h2 className="font-semibold text-slate-800 mb-1">ポートフォリオ分析</h2>
            <p className="text-xs text-slate-400 mb-4">
              評価額（取得できない銘柄は取得金額で代用）に基づく機械的な参考情報であり、投資助言ではありません。
            </p>

            <div className="mb-4 flex flex-col gap-2">
              {portfolio.suggestions.map((suggestion, i) => (
                <div key={i} className="flex items-start gap-2 text-sm bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-3 py-2">
                  <span className="mt-0.5">💡</span>
                  <span>{suggestion}</span>
                </div>
              ))}
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <div className="text-xs text-slate-500 mb-2">銘柄別配分</div>
                <div className="flex flex-col gap-2">
                  {portfolio.by_stock.map((s) => (
                    <div key={s.name}>
                      <div className="flex justify-between text-xs text-slate-600 mb-0.5">
                        <span>{s.name}</span>
                        <span className="font-mono">{s.share}%</span>
                      </div>
                      <div className="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div className="h-full bg-indigo-500 rounded-full" style={{ width: `${s.share}%` }} />
                      </div>
                    </div>
                  ))}
                </div>
              </div>
              <div>
                <div className="text-xs text-slate-500 mb-2">テーマ別配分</div>
                <div className="flex flex-col gap-2">
                  {portfolio.by_theme.map((t) => (
                    <div key={t.name}>
                      <div className="flex justify-between text-xs text-slate-600 mb-0.5">
                        <span>{t.name}</span>
                        <span className="font-mono">{t.share}%</span>
                      </div>
                      <div className="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div className="h-full bg-amber-500 rounded-full" style={{ width: `${t.share}%` }} />
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>

          <div className="border border-slate-200 rounded-2xl shadow-sm bg-white p-4 mb-8 max-w-md">
            <TrendChart config={allocationChartConfig} />
          </div>

          <p className="text-xs text-slate-400 mb-2">※現在値は無料プランのため最新から遅延したデータです（リアルタイムではありません）。</p>
          <div className="border border-slate-200 rounded-2xl shadow-sm bg-white overflow-x-auto">
            <table className="w-full text-sm border-collapse min-w-[900px]">
              <thead>
                <tr className="border-b border-slate-200 bg-slate-50 text-left">
                  <th className="py-2.5 px-3 text-slate-500 font-medium">銘柄</th>
                  <th className="py-2.5 px-3 text-slate-500 font-medium">上場市場</th>
                  <th className="py-2.5 px-3 text-right text-slate-500 font-medium">株数</th>
                  <th className="py-2.5 px-3 text-right text-slate-500 font-medium">取得単価</th>
                  <th className="py-2.5 px-3 text-right text-slate-500 font-medium">取得金額</th>
                  <th className="py-2.5 px-3 text-right text-slate-500 font-medium">現在値</th>
                  <th className="py-2.5 px-3 text-right text-slate-500 font-medium">評価額</th>
                  <th className="py-2.5 px-3 text-right text-slate-500 font-medium">評価損益</th>
                  <th className="py-2.5 px-3 text-slate-500 font-medium">投資判断</th>
                  <th className="py-2.5 px-3 text-slate-500 font-medium">テーマ</th>
                  <th className="py-2.5 px-3" />
                </tr>
              </thead>
              <tbody className="font-mono">
                {holdings.map((row) => {
                  const holding = row.holding;
                  return (
                    <tr
                      key={holding.id}
                      className="border-b border-slate-100 last:border-0 odd:bg-white even:bg-slate-50/60 hover:bg-indigo-50/60 transition-colors"
                    >
                      <td className="py-2 px-3 font-sans">
                        <Link className="text-indigo-600 hover:underline font-medium" href={`/stocks/${holding.code}`}>
                          {holding.stock?.stockName}（{holding.code}）
                        </Link>
                      </td>
                      <td className="py-2 px-3 font-sans text-slate-500">{holding.stock?.market?.market ?? "—"}</td>
                      <td className="py-2 px-3 text-right">{formatNumber(holding.shares)}</td>
                      <td className="py-2 px-3 text-right">{formatNumber(holding.average_acquisition_price, 2)}</td>
                      <td className="py-2 px-3 text-right">{formatNumber(holding.acquisition_cost)}</td>
                      <td className="py-2 px-3 text-right">
                        {row.current_price !== null ? (
                          <>
                            {formatNumber(row.current_price, 1)}
                            {row.price_change !== null && (
                              <span
                                className={`block text-xs ${
                                  row.price_change > 0 ? "text-rose-600" : row.price_change < 0 ? "text-emerald-600" : "text-slate-400"
                                }`}
                              >
                                {row.price_change > 0 ? "+" : ""}
                                {row.price_change_percent}%
                              </span>
                            )}
                            <span className="block text-[10px] font-sans text-slate-400 mt-0.5">
                              {row.price_date ? `${row.price_date}時点` : ""}
                              {row.computed_at ? `／取得${relativeTimeJa(row.computed_at)}` : ""}
                            </span>
                          </>
                        ) : (
                          <Link href={`/stocks/${holding.code}`} className="font-sans text-slate-400 hover:text-indigo-600 hover:underline text-xs">
                            未同期（開くと取得）
                          </Link>
                        )}
                      </td>
                      <td className="py-2 px-3 text-right">{row.market_value !== null ? formatNumber(row.market_value) : "—"}</td>
                      <td className={`py-2 px-3 text-right font-semibold ${(row.unrealized_pl ?? 0) < 0 ? "text-emerald-600" : "text-rose-600"}`}>
                        {row.unrealized_pl !== null ? formatNumber(row.unrealized_pl) : "—"}
                      </td>
                      <td className="py-2 px-3 font-sans">
                        <ScoreBadge score={holding.stock?.score} showPoints={false} />
                      </td>
                      <td className="py-2 px-3 font-sans">
                        {holding.stock?.themes?.map((theme) => (
                          <span key={theme.id} className="inline-block bg-slate-100 text-slate-600 rounded-full px-2 py-0.5 text-xs mr-1 whitespace-nowrap">
                            {theme.name}
                          </span>
                        ))}
                      </td>
                      <td className="py-2 px-3 text-right font-sans">
                        <Link className="text-indigo-600 hover:underline" href={`/sbi-holdings/${holding.id}/edit`}>
                          編集
                        </Link>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </>
      )}
    </>
  );
}
