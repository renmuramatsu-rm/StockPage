"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { createTheme, ThemeInput } from "@/lib/api";
import { useRequireAuth } from "@/lib/useAuth";
import ThemeForm from "@/components/ThemeForm";

export default function NewThemePage() {
  const user = useRequireAuth();
  const router = useRouter();
  if (!user) return null;

  async function handleSubmit(data: ThemeInput) {
    const { theme } = await createTheme(data);
    router.push(`/themes/${theme.id}`);
  }

  return (
    <>
      <div className="mb-3">
        <Link href="/" className="text-sm text-slate-500 hover:text-indigo-600">
          &larr; ダッシュボードへ
        </Link>
      </div>
      <h1 className="text-2xl font-bold mb-6 text-slate-900">新しいテーマ</h1>
      <ThemeForm onSubmit={handleSubmit} />
    </>
  );
}
