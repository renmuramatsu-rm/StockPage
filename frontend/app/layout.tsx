import type { Metadata } from "next";
import "./globals.css";
import NavBar from "@/components/NavBar";

export const metadata: Metadata = {
  title: "株式分析.",
  description: "テーマ別・財務データに基づく国内株分析サイト",
};

export default function RootLayout({ children }: LayoutProps<"/">) {
  return (
    <html lang="ja" className="h-full antialiased">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
        <link
          href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap"
          rel="stylesheet"
        />
      </head>
      <body className="min-h-full flex flex-col bg-slate-100 text-slate-900" style={{ fontFamily: '"Noto Sans JP", sans-serif' }}>
        <NavBar />
        <main className="max-w-6xl mx-auto p-6 w-full flex-1">{children}</main>
        <footer className="max-w-6xl mx-auto px-6 py-8 text-center text-xs text-slate-400">
          データ提供: J-Quants（JPX）・投資判断は機械的な参考指標であり、投資助言ではありません。
        </footer>
      </body>
    </html>
  );
}
