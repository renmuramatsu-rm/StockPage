"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { getUser } from "@/lib/api";

export default function LoginCallbackPage() {
  const [failed, setFailed] = useState(false);

  useEffect(() => {
    let cancelled = false;

    getUser().then((user) => {
      if (cancelled) return;
      if (user) {
        window.location.href = "/";
      } else {
        setFailed(true);
      }
    });

    return () => {
      cancelled = true;
    };
  }, []);

  return (
    <div className="flex justify-center py-16">
      <div className="text-center">
        {failed ? (
          <>
            <p className="text-slate-700 mb-4">ログインを確認できませんでした。</p>
            <Link href="/login" className="text-indigo-600 hover:underline">
              ログインページへ戻る
            </Link>
          </>
        ) : (
          <p className="text-slate-500">ログインを確認しています…</p>
        )}
      </div>
    </div>
  );
}
