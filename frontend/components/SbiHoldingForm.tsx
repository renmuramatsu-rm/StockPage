"use client";

import { useState } from "react";
import { SbiHolding, SbiHoldingInput } from "@/lib/api";
import StockSearch from "@/components/StockSearch";

export default function SbiHoldingForm({
  holding,
  stocks,
  onSubmit,
}: {
  holding?: SbiHolding;
  stocks: { code: string; stockName: string }[];
  onSubmit: (data: SbiHoldingInput) => Promise<void>;
}) {
  const [code, setCode] = useState(holding?.code ?? "");
  const [shares, setShares] = useState(holding?.shares?.toString() ?? "");
  const [price, setPrice] = useState(holding?.average_acquisition_price ?? "");
  const [acquisitionDate, setAcquisitionDate] = useState(holding?.acquisition_date ?? "");
  const [accountType, setAccountType] = useState(holding?.account_type ?? "");
  const [memo, setMemo] = useState(holding?.memo ?? "");
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function handleSubmit(event: React.FormEvent) {
    event.preventDefault();
    setError(null);
    setSubmitting(true);
    try {
      await onSubmit({
        code,
        shares: Number(shares),
        average_acquisition_price: Number(price),
        acquisition_date: acquisitionDate || undefined,
        account_type: (accountType || "") as SbiHoldingInput["account_type"],
        memo: memo || undefined,
      });
    } catch (err) {
      setError(err instanceof Error ? err.message : "保存に失敗しました。");
      setSubmitting(false);
    }
  }

  return (
    <form onSubmit={handleSubmit} className="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 max-w-lg">
      {error && (
        <p className="text-red-600 text-sm mb-4 bg-red-50 border border-red-100 rounded-lg px-3 py-2">{error}</p>
      )}

      <div className="mb-4">
        <label className="block text-sm font-medium mb-1 text-slate-700">銘柄コード</label>
        {holding ? (
          <>
            <div className="border border-slate-200 rounded-lg w-full px-3 py-2 bg-slate-50 text-slate-600">
              {holding.code} - {holding.stock?.stockName}
            </div>
            <p className="text-xs text-slate-400 mt-1">銘柄の変更はできません。別の銘柄で登録し直してください。</p>
          </>
        ) : (
          <StockSearch stocks={stocks} value={code} onChange={setCode} />
        )}
      </div>

      <div className="mb-4">
        <label className="block text-sm font-medium mb-1 text-slate-700" htmlFor="shares">
          株数
        </label>
        <input
          className="border border-slate-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          type="number"
          min={1}
          id="shares"
          value={shares}
          onChange={(e) => setShares(e.target.value)}
          required
        />
      </div>

      <div className="mb-4">
        <label className="block text-sm font-medium mb-1 text-slate-700" htmlFor="average_acquisition_price">
          取得単価（平均）
        </label>
        <input
          className="border border-slate-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          type="number"
          step="0.01"
          min={0}
          id="average_acquisition_price"
          value={price}
          onChange={(e) => setPrice(e.target.value)}
          required
        />
      </div>

      <div className="mb-4">
        <label className="block text-sm font-medium mb-1 text-slate-700" htmlFor="acquisition_date">
          取得日
        </label>
        <input
          className="border border-slate-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          type="date"
          id="acquisition_date"
          value={acquisitionDate}
          onChange={(e) => setAcquisitionDate(e.target.value)}
        />
      </div>

      <div className="mb-4">
        <label className="block text-sm font-medium mb-1 text-slate-700" htmlFor="account_type">
          口座区分
        </label>
        <select
          className="border border-slate-300 rounded-lg w-full px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          id="account_type"
          value={accountType ?? ""}
          onChange={(e) => setAccountType(e.target.value as typeof accountType)}
        >
          <option value="">未選択</option>
          <option value="specific">特定口座</option>
          <option value="general">一般口座</option>
          <option value="nisa">NISA</option>
        </select>
      </div>

      <div className="mb-6">
        <label className="block text-sm font-medium mb-1 text-slate-700" htmlFor="memo">
          メモ
        </label>
        <textarea
          className="border border-slate-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          id="memo"
          rows={2}
          value={memo ?? ""}
          onChange={(e) => setMemo(e.target.value)}
        />
      </div>

      <button
        type="submit"
        disabled={submitting}
        className="bg-indigo-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-indigo-700 transition disabled:opacity-60"
      >
        {submitting ? "保存中…" : "保存"}
      </button>
    </form>
  );
}
