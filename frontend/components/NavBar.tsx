"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { logout } from "@/lib/api";
import { useCurrentUser } from "@/lib/useAuth";

type NavLink = { href: string; label: string; activePrefix: string };

export default function NavBar() {
  const user = useCurrentUser();
  const pathname = usePathname();

  const links: NavLink[] = [];
  if (user) {
    links.push({ href: "/", label: "テーマ別", activePrefix: "/themes" });
  }
  links.push({ href: "/stocks", label: "銘柄一覧", activePrefix: "/stocks" });
  links.push({ href: "/glossary", label: "指標の見方", activePrefix: "/glossary" });
  if (user) {
    links.push({ href: "/favorites", label: "お気に入り", activePrefix: "/favorites" });
    links.push({ href: "/sbi-holdings", label: "SBI保有", activePrefix: "/sbi-holdings" });
  }

  const isActive = (link: NavLink) =>
    link.href === "/" ? pathname === "/" : pathname.startsWith(link.activePrefix);

  return (
    <header className="bg-slate-900 sticky top-0 z-20 shadow-lg shadow-slate-900/10">
      <div className="max-w-6xl mx-auto px-6 py-3.5 flex items-center justify-between flex-wrap gap-3">
        <Link
          href={user ? "/" : "/stocks"}
          className="flex items-center gap-2 text-lg font-bold text-white tracking-tight"
        >
          <span className="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-amber-500 text-slate-900 font-extrabold text-sm">
            株
          </span>
          <span>
            株式分析<span className="text-amber-400">.</span>
          </span>
        </Link>

        <div className="flex items-center gap-4">
          <nav>
            <ul className="flex gap-1">
              {links.map((link) => (
                <li key={link.href}>
                  <Link
                    href={link.href}
                    className={`px-3.5 py-1.5 rounded-lg text-sm font-medium transition ${
                      isActive(link)
                        ? "bg-white/10 text-amber-300"
                        : "text-slate-300 hover:bg-white/5 hover:text-white"
                    }`}
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </nav>
          <div className="w-px h-5 bg-white/10" />
          {user === undefined ? null : user ? (
            <button
              type="button"
              onClick={async () => {
                await logout();
                window.location.href = "/login";
              }}
              className="text-sm text-slate-300 hover:text-white transition"
            >
              ログアウト
            </button>
          ) : (
            <>
              <Link href="/login" className="text-sm text-slate-300 hover:text-white transition">
                ログイン
              </Link>
              <Link href="/register" className="text-sm font-medium text-amber-300 hover:text-amber-200 transition">
                新規登録
              </Link>
            </>
          )}
        </div>
      </div>
    </header>
  );
}
