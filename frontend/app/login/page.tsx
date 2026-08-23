"use client";

import { useState, Suspense } from "react";
import { useSearchParams } from "next/navigation";
import Link from "next/link";
import { LoginError, googleLoginUrl, login } from "@/lib/api";

const ERROR_MESSAGES: Record<string, string> = {
  google_failed: "Googleログインに失敗しました。もう一度お試しください。",
  not_allowed: "このGoogleアカウントではログインできません。",
};

function LoginForm() {
  const searchParams = useSearchParams();
  const queryError = searchParams.get("error");

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [remember, setRemember] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(
    queryError ? ERROR_MESSAGES[queryError] ?? "ログインに失敗しました。" : null
  );

  async function handleSubmit(event: React.FormEvent) {
    event.preventDefault();
    setError(null);
    setSubmitting(true);

    try {
      await login(email, password, remember);
      window.location.href = "/";
    } catch (err) {
      setError(err instanceof LoginError ? err.message : "ログインに失敗しました。");
      setSubmitting(false);
    }
  }

  return (
    <div className="w-full max-w-sm">
      <h1 className="text-2xl font-bold text-slate-900 mb-2">ログイン</h1>
      <p className="text-sm text-slate-500 mb-6">
        SBI保有銘柄の登録・編集やテーマ管理にはログインが必要です。閲覧だけならログイン不要です。
      </p>

      <div className="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
        <a
          href={googleLoginUrl()}
          className="w-full flex items-center justify-center gap-2 border border-slate-300 rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition mb-5"
        >
          <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true">
            <path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.62z" />
            <path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.83.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.98v2.33A9 9 0 0 0 9 18z" />
            <path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.66 9c0-.59.1-1.17.29-1.7V4.97H.98A9 9 0 0 0 0 9c0 1.45.35 2.83.98 4.03l2.97-2.33z" />
            <path fill="#EA4335" d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .98 4.97l2.97 2.33C4.66 5.17 6.65 3.58 9 3.58z" />
          </svg>
          Googleでログイン
        </a>

        <div className="flex items-center gap-3 mb-5">
          <div className="h-px bg-slate-200 flex-1" />
          <span className="text-xs text-slate-400">または</span>
          <div className="h-px bg-slate-200 flex-1" />
        </div>

        <form onSubmit={handleSubmit}>
          {error && (
            <p className="text-red-600 text-sm mb-4 bg-red-50 border border-red-100 rounded-lg px-3 py-2">
              {error}
            </p>
          )}

          <div className="mb-4">
            <label className="block text-sm font-medium mb-1 text-slate-700" htmlFor="email">
              メールアドレス
            </label>
            <input
              className="border border-slate-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              type="email"
              id="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              autoFocus
            />
          </div>

          <div className="mb-4">
            <label className="block text-sm font-medium mb-1 text-slate-700" htmlFor="password">
              パスワード
            </label>
            <input
              className="border border-slate-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              type="password"
              id="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </div>

          <label className="flex items-center gap-2 mb-6 text-sm text-slate-600">
            <input
              type="checkbox"
              checked={remember}
              onChange={(e) => setRemember(e.target.checked)}
              className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
            />
            ログイン状態を保持する
          </label>

          <button
            type="submit"
            disabled={submitting}
            className="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-indigo-700 transition disabled:opacity-60"
          >
            {submitting ? "ログイン中…" : "ログイン"}
          </button>
        </form>

        <p className="text-sm text-slate-500 mt-5 text-center">
          アカウントをお持ちでない方は{" "}
          <Link href="/register" className="text-indigo-600 hover:underline">
            新規登録
          </Link>
        </p>
      </div>
    </div>
  );
}

export default function LoginPage() {
  return (
    <div className="flex justify-center py-8">
      <Suspense fallback={null}>
        <LoginForm />
      </Suspense>
    </div>
  );
}
