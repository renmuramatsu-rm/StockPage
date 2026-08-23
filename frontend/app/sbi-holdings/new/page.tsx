"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { Stock, createSbiHolding, getSbiHoldingStocks } from "@/lib/api";
import { useRequireAuth } from "@/lib/useAuth";
import SbiHoldingForm from "@/components/SbiHoldingForm";

export default function NewSbiHoldingPage() {
  const user = useRequireAuth();
  const router = useRouter();
  const [stocks, setStocks] = useState<Pick<Stock, "code" | "stockName">[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!user) return;
    getSbiHoldingStocks()
      .then((data) => setStocks(data.stocks))
      .catch((e) => setError(e instanceof Error ? e.message : "読み込みに失敗しました。"));
  }, [user]);

  if (!user) return null;
  if (error) return <p className="text-red-600 bg-red-50 border border-red-200 rounded-lg p-4">{error}</p>;
  if (!stocks) return <p className="text-slate-500">読み込んでいます…</p>;

  return (
    <>
      <div className="mb-3">
        <Link href="/sbi-holdings" className="text-sm text-slate-500 hover:text-indigo-600">
          &larr; SBI保有銘柄へ
        </Link>
      </div>
      <h1 className="text-2xl font-bold mb-6 text-slate-900">保有銘柄を追加</h1>

      <SbiHoldingForm
        stocks={stocks}
        onSubmit={async (data) => {
          await createSbiHolding(data);
          router.push("/sbi-holdings");
        }}
      />
    </>
  );
}
