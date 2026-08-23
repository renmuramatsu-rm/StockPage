"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useParams, useRouter } from "next/navigation";
import { SbiHolding, deleteSbiHolding, getSbiHolding, updateSbiHolding } from "@/lib/api";
import { useRequireAuth } from "@/lib/useAuth";
import SbiHoldingForm from "@/components/SbiHoldingForm";

export default function EditSbiHoldingPage() {
  const user = useRequireAuth();
  const router = useRouter();
  const { id } = useParams<{ id: string }>();
  const [holding, setHolding] = useState<SbiHolding | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [deleting, setDeleting] = useState(false);

  useEffect(() => {
    if (!user) return;
    getSbiHolding(id)
      .then((data) => setHolding(data.holding))
      .catch((e) => setError(e instanceof Error ? e.message : "読み込みに失敗しました。"));
  }, [user, id]);

  if (!user) return null;
  if (error) return <p className="text-red-600 bg-red-50 border border-red-200 rounded-lg p-4">{error}</p>;
  if (!holding) return <p className="text-slate-500">読み込んでいます…</p>;

  async function handleDelete() {
    if (!confirm("この保有銘柄を削除しますか？")) return;
    setDeleting(true);
    try {
      await deleteSbiHolding(Number(id));
      router.push("/sbi-holdings");
    } catch (e) {
      setError(e instanceof Error ? e.message : "削除に失敗しました。");
      setDeleting(false);
    }
  }

  return (
    <>
      <div className="mb-3">
        <Link href="/sbi-holdings" className="text-sm text-slate-500 hover:text-indigo-600">
          &larr; SBI保有銘柄へ
        </Link>
      </div>
      <h1 className="text-2xl font-bold mb-6 text-slate-900">保有銘柄を編集</h1>

      <SbiHoldingForm
        holding={holding}
        stocks={[]}
        onSubmit={async (data) => {
          await updateSbiHolding(Number(id), data);
          router.push("/sbi-holdings");
        }}
      />

      <div className="max-w-lg mt-4">
        <button
          type="button"
          onClick={handleDelete}
          disabled={deleting}
          className="text-sm text-red-600 hover:underline disabled:opacity-60"
        >
          この保有銘柄を削除
        </button>
      </div>
    </>
  );
}
