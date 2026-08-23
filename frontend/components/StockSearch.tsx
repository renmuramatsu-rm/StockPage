"use client";

import { useEffect, useMemo, useRef, useState } from "react";

type StockOption = { code: string; stockName: string };

export default function StockSearch({
  stocks,
  value,
  onChange,
  placeholder = "銘柄コードまたは銘柄名で検索",
}: {
  stocks: StockOption[];
  value: string;
  onChange: (code: string) => void;
  placeholder?: string;
}) {
  const selected = useMemo(() => stocks.find((s) => s.code === value) ?? null, [stocks, value]);
  const [query, setQuery] = useState(selected ? `${selected.code} - ${selected.stockName}` : "");
  const [open, setOpen] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
        setOpen(false);
      }
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  const results = useMemo(() => {
    const term = query.trim().toLowerCase();
    if (!term) return stocks.slice(0, 30);
    return stocks
      .filter((s) => s.code.toLowerCase().includes(term) || s.stockName.toLowerCase().includes(term))
      .slice(0, 30);
  }, [stocks, query]);

  return (
    <div className="relative" ref={containerRef}>
      <input
        type="text"
        autoComplete="off"
        placeholder={placeholder}
        value={query}
        onFocus={() => setOpen(true)}
        onChange={(e) => {
          setQuery(e.target.value);
          setOpen(true);
          if (value) onChange("");
        }}
        className="border border-slate-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
      />
      {open && results.length > 0 && (
        <div className="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-64 overflow-y-auto">
          {results.map((s) => (
            <button
              type="button"
              key={s.code}
              onClick={() => {
                onChange(s.code);
                setQuery(`${s.code} - ${s.stockName}`);
                setOpen(false);
              }}
              className="w-full text-left px-3 py-2 text-sm hover:bg-indigo-50 flex gap-2"
            >
              <span className="font-mono text-slate-500">{s.code}</span>
              <span className="text-slate-800">{s.stockName}</span>
            </button>
          ))}
        </div>
      )}
    </div>
  );
}
