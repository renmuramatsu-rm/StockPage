"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useParams, useRouter } from "next/navigation";
import { Theme, ThemeInput, deleteTheme, getTheme, updateTheme } from "@/lib/api";
import { useRequireAuth } from "@/lib/useAuth";
import ThemeForm from "@/components/ThemeForm";

export default function EditThemePage() {
  const user = useRequireAuth();
  const router = useRouter();
  const { id } = useParams<{ id: string }>();
  const [theme, setTheme] = useState<Theme | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [deleting, setDeleting] = useState(false);

  useEffect(() => {
    if (!user) return;
    getTheme(id)
      .then((data) => setTheme(data.theme))
      .catch((e) => setError(e instanceof Error ? e.message : "読み込みに失敗しました。"));
  }, [user, id]);

  if (!user) return null;
  if (error) return <p className="text-red-600 bg-red-50 border border-red-200 rounded-lg p-4">{error}</p>;
  if (!theme) return <p className="text-slate-500">読み込んでいます…</p>;

  async function handleSubmit(data: ThemeInput) {
    await updateTheme(id, data);
    router.push(`/themes/${id}`);
  }

  async function handleDelete() {
    if (!confirm("このテーマを削除しますか？")) return;
    setDeleting(true);
    try {
      await deleteTheme(id);
      router.push("/");
    } catch (e) {
      setError(e instanceof Error ? e.message : "削除に失敗しました。");
      setDeleting(false);
    }
  }

  return (
    <>
      <div className="mb-3">
        <Link href="/" className="text-sm text-slate-500 hover:text-indigo-600">
          &larr; ダッシュボードへ
        </Link>
      </div>
      <h1 className="text-2xl font-bold mb-6 text-slate-900">テーマを編集</h1>

      <ThemeForm theme={theme} onSubmit={handleSubmit} />

      <div className="max-w-lg mt-4">
        <button
          type="button"
          onClick={handleDelete}
          disabled={deleting}
          className="text-sm text-red-600 hover:underline disabled:opacity-60"
        >
          このテーマを削除
        </button>
      </div>
    </>
  );
}
