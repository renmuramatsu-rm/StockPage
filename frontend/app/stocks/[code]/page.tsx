"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useParams } from "next/navigation";
import { StockShowData, getStock } from "@/lib/api";
import { formatNumber } from "@/lib/format";
import { useCurrentUser } from "@/lib/useAuth";
import TrendChart from "@/components/TrendChart";
import ScoreBadge from "@/components/ScoreBadge";
import FavoriteButton from "@/components/FavoriteButton";

export default function StockShowPage() {
  const user = useCurrentUser();
  const { code } = useParams<{ code: string }>();
  const [data, setData] = useState<StockShowData | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    getStock(code)
      .then(setData)
      .catch((e) => setError(e instanceof Error ? e.message : "読み込みに失敗しました。"));
  }, [code]);

  if (error) return <p className="text-red-600 bg-red-50 border border-red-200 rounded-lg p-4">{error}</p>;
  if (!data) return <p className="text-slate-500">読み込んでいます…</p>;

  const { stock, trendRows, cagr, cagrYears, salesChartConfig, profitChartConfig, ratioChartConfig, syncError, scoreRecord, overview } = data;
  const q = encodeURIComponent(stock.stockName);
  const priceChange = scoreRecord.price_change !== null ? parseFloat(scoreRecord.price_change) : null;

  const cagrCard = (label: string, value: number | null) => (
    <div className="border border-slate-200 rounded-2xl shadow-sm p-4 bg-white">
      <div className="text-sm text-slate-500">
        {label} CAGR（過去{cagrYears}年）
      </div>
      <div className={`text-2xl font-bold font-mono mt-0.5 ${(value ?? 0) < 0 ? "text-rose-600" : "text-slate-900"}`}>
        {value !== null ? `${value}%` : "—"}
      </div>
    </div>
  );

  return (
    <>
      <div className="mb-3">
        <Link href="/stocks" className="text-sm text-slate-500 hover:text-indigo-600">
          &larr; 銘柄一覧へ
        </Link>
      </div>

      <div className="mb-6 flex items-start justify-between flex-wrap gap-2">
        <div>
          <h1 className="text-2xl font-bold text-slate-900 flex items-center gap-2">
            {stock.stockName} <span className="text-slate-400 font-mono text-lg">{stock.code}</span>
            {user && <FavoriteButton code={stock.code} initialFavorited={data.isFavorited} />}
          </h1>
          <p className="text-slate-500 text-sm mt-0.5">{stock.market?.market}</p>
        </div>
        {user && (
          <Link href={`/stocks/${stock.code}/themes`} className="text-sm font-medium text-indigo-600 hover:text-indigo-800">
            テーマを編集
          </Link>
        )}
      </div>

      <div className="mb-6 flex flex-wrap gap-2">
        {stock.themes && stock.themes.length > 0 ? (
          stock.themes.map((theme) => (
            <span key={theme.id} className="inline-block bg-slate-100 text-slate-600 rounded-full px-3 py-1 text-xs">
              {theme.name}
            </span>
          ))
        ) : (
          <span className="text-slate-400 text-sm">テーマ未設定</span>
        )}
      </div>

      {syncError && <p className="text-sm text-red-700 mb-6 bg-red-50 border border-red-200 rounded-lg p-3">{syncError}</p>}

      <div className="border border-slate-200 rounded-2xl shadow-sm p-5 mb-6 bg-white">
        <h2 className="text-sm font-semibold text-slate-700 mb-2">会社概要</h2>
        <p className="text-sm text-slate-600 leading-relaxed mb-4">{overview}</p>
        <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm border-t border-slate-100 pt-4">
          <div>
            <div className="text-xs text-slate-400">銘柄コード</div>
            <div className="font-mono font-medium text-slate-800">{stock.code}</div>
          </div>
          <div>
            <div className="text-xs text-slate-400">上場市場</div>
            <div className="font-medium text-slate-800">{stock.market?.market ?? "—"}</div>
          </div>
          <div>
            <div className="text-xs text-slate-400">規模区分</div>
            <div className="font-medium text-slate-800">{stock.scale_category ?? "—"}</div>
          </div>
          <div>
            <div className="text-xs text-slate-400">テーマ</div>
            <div className="font-medium text-slate-800">
              {stock.themes && stock.themes.length > 0 ? stock.themes.map((t) => t.name).join("、") : "—"}
            </div>
          </div>
        </div>
      </div>

      <div className="border border-slate-200 rounded-2xl shadow-sm p-5 mb-6 bg-white">
        <div className="flex items-center justify-between flex-wrap gap-3">
          <div>
            <div className="text-xs text-slate-500 mb-1.5">投資判断スコア（機械的な参考指標であり、投資助言ではありません）</div>
            <ScoreBadge score={scoreRecord} className="text-sm px-3 py-1" />
          </div>
          <div className="text-sm text-right">
            {scoreRecord.current_price !== null ? (
              <>
                <div>
                  <span className="text-xl font-bold text-slate-900 font-mono">{formatNumber(scoreRecord.current_price, 1)}</span>
                  <span className="text-slate-400 text-xs">円</span>
                  {priceChange !== null && (
                    <span
                      className={`ml-1.5 font-semibold ${
                        priceChange > 0 ? "text-rose-600" : priceChange < 0 ? "text-emerald-600" : "text-slate-500"
                      }`}
                    >
                      {priceChange > 0 ? "+" : ""}
                      {scoreRecord.price_change} ({priceChange > 0 ? "+" : ""}
                      {scoreRecord.price_change_percent}%)
                    </span>
                  )}
                </div>
                <div className="text-slate-400 text-xs mt-0.5">
                  {scoreRecord.price_date ? new Date(scoreRecord.price_date).toLocaleDateString("ja-JP") : ""}時点
                  {scoreRecord.per !== null && <span className="ml-2">PER {scoreRecord.per}倍</span>}
                  {scoreRecord.pbr !== null && <span className="ml-2">PBR {scoreRecord.pbr}倍</span>}
                </div>
                <p className="text-[11px] text-slate-400 mt-1">※無料プランのため株価は最新から遅延したデータです（リアルタイムではありません）</p>
              </>
            ) : (
              <span className="text-slate-400">株価取得失敗</span>
            )}
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4 text-sm">
          <div className="bg-slate-50 rounded-xl p-3.5">
            <div className="text-slate-500 text-xs">成長性</div>
            <div className="font-semibold text-slate-800 mt-0.5">
              {scoreRecord.growth_label}
              {scoreRecord.growth_score !== null ? `（${scoreRecord.growth_score}点）` : ""}
            </div>
          </div>
          <div className="bg-slate-50 rounded-xl p-3.5">
            <div className="text-slate-500 text-xs">割安性（PER・PBR）</div>
            <div className="font-semibold text-slate-800 mt-0.5">
              {scoreRecord.valuation_label}
              {scoreRecord.valuation_score !== null ? `（${scoreRecord.valuation_score}点）` : ""}
            </div>
          </div>
          <div className="bg-slate-50 rounded-xl p-3.5">
            <div className="text-slate-500 text-xs">収益性・財務健全性</div>
            <div className="font-semibold text-slate-800 mt-0.5">
              {scoreRecord.quality_label}
              {scoreRecord.quality_score !== null ? `（${scoreRecord.quality_score}点）` : ""}
            </div>
          </div>
        </div>
      </div>

      <div className="mb-6">
        <div className="text-xs text-slate-500 mb-2">
          最新トピック・関連ニュース（外部サイトを開きます。著作権の関係でこのサイト内に記事は表示していません）
        </div>
        <div className="flex flex-wrap gap-2 text-sm">
          <a
            href={`https://finance.yahoo.co.jp/quote/${stock.code}.T/disclosure`}
            target="_blank"
            rel="noopener"
            className="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition font-medium"
          >
            適時開示情報（決算・IR発表）
          </a>
          <a
            href={`https://finance.yahoo.co.jp/quote/${stock.code}.T/news`}
            target="_blank"
            rel="noopener"
            className="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition"
          >
            Yahoo!ファイナンスのニュース
          </a>
          <a
            href={`https://www.google.com/search?q=site:shikiho.toyokeizai.net+${q}`}
            target="_blank"
            rel="noopener"
            className="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition"
          >
            会社四季報オンラインで検索
          </a>
          <a
            href={`https://www.google.com/search?q=site:nikkei.com+${q}+株価`}
            target="_blank"
            rel="noopener"
            className="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition"
          >
            日経電子版で検索
          </a>
          <a
            href={`https://www.google.com/search?q=${q}+${stock.code}&tbm=nws`}
            target="_blank"
            rel="noopener"
            className="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition"
          >
            Googleニュースで検索
          </a>
        </div>
      </div>

      {trendRows.length === 0 ? (
        <p className="text-slate-500 mb-6">
          財務データがまだありません。
          <code className="bg-slate-100 px-1.5 py-0.5 rounded text-xs">php artisan financials:sync {stock.code}</code>{" "}
          を実行するか、時間をおいてこのページを開き直してください。
        </p>
      ) : (
        <>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
            {cagrCard("売上高", cagr.net_sales)}
            {cagrCard("営業利益", cagr.operating_profit)}
            {cagrCard("純利益", cagr.profit)}
          </div>

          {cagrYears < 3 && (
            <p className="text-xs text-amber-600 mb-6">
              ※現在のJ-Quantsプランでは過去{cagrYears}年分のデータしか取得できていないため、参考値です。3年以上の推移を見るにはJ-Quantsの有料プラン（Light以上）への切り替えが必要です。
            </p>
          )}

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div className="border border-slate-200 rounded-2xl shadow-sm p-4 bg-white">
              <TrendChart config={salesChartConfig} />
            </div>
            <div className="border border-slate-200 rounded-2xl shadow-sm p-4 bg-white">
              <TrendChart config={profitChartConfig} />
            </div>
            <div className="border border-slate-200 rounded-2xl shadow-sm p-4 bg-white lg:col-span-2">
              <TrendChart config={ratioChartConfig} height={120} />
            </div>
          </div>

          <div className="border border-slate-200 rounded-2xl shadow-sm bg-white overflow-x-auto">
            <table className="w-full text-sm border-collapse min-w-[720px]">
              <thead>
                <tr className="border-b border-slate-200 bg-slate-50 text-left">
                  <th className="py-2.5 px-3 text-slate-500 font-medium">年度</th>
                  <th className="py-2.5 px-3 text-right text-slate-500 font-medium">売上高</th>
                  <th className="py-2.5 px-3 text-right text-slate-500 font-medium">前年比</th>
                  <th className="py-2.5 px-3 text-right text-slate-500 font-medium">営業利益</th>
                  <th className="py-2.5 px-3 text-right text-slate-500 font-medium">営業利益率</th>
                  <th className="py-2.5 px-3 text-right text-slate-500 font-medium">純利益</th>
                  <th className="py-2.5 px-3 text-right text-slate-500 font-medium">EPS</th>
                  <th className="py-2.5 px-3 text-right text-slate-500 font-medium">ROE</th>
                  <th className="py-2.5 px-3 text-right text-slate-500 font-medium">自己資本比率</th>
                </tr>
              </thead>
              <tbody className="font-mono">
                {trendRows.map((row) => (
                  <tr
                    key={row.fiscal_year}
                    className="border-b border-slate-100 last:border-0 odd:bg-white even:bg-slate-50/60 hover:bg-indigo-50/60 transition-colors"
                  >
                    <td className="py-2 px-3 font-sans font-medium text-slate-800">{row.fiscal_year}</td>
                    <td className="py-2 px-3 text-right">{row.net_sales !== null ? formatNumber(row.net_sales) : "—"}</td>
                    <td className={`py-2 px-3 text-right ${(row.yoy_net_sales ?? 0) < 0 ? "text-rose-600" : "text-slate-600"}`}>
                      {row.yoy_net_sales !== null ? `${row.yoy_net_sales}%` : "—"}
                    </td>
                    <td className="py-2 px-3 text-right">{row.operating_profit !== null ? formatNumber(row.operating_profit) : "—"}</td>
                    <td className="py-2 px-3 text-right text-slate-600">{row.operating_margin !== null ? `${row.operating_margin}%` : "—"}</td>
                    <td className="py-2 px-3 text-right">{row.profit !== null ? formatNumber(row.profit) : "—"}</td>
                    <td className="py-2 px-3 text-right text-slate-600">{row.eps ?? "—"}</td>
                    <td className="py-2 px-3 text-right text-slate-600">{row.roe !== null ? `${row.roe}%` : "—"}</td>
                    <td className="py-2 px-3 text-right text-slate-600">{row.equity_ratio !== null ? `${row.equity_ratio}%` : "—"}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </>
      )}
    </>
  );
}
