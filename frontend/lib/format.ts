export function formatNumber(value: number | string | null | undefined, decimals = 0): string {
  if (value === null || value === undefined) return "—";
  const n = typeof value === "string" ? parseFloat(value) : value;
  if (Number.isNaN(n)) return "—";
  return n.toLocaleString("ja-JP", { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
}

/** Rough equivalent of Laravel Carbon's diffForHumans(), Japanese only. */
export function relativeTimeJa(dateString: string | null | undefined): string {
  if (!dateString) return "未実行";

  const date = new Date(dateString);
  if (Number.isNaN(date.getTime())) return "未実行";

  const diffSeconds = Math.round((Date.now() - date.getTime()) / 1000);
  const abs = Math.abs(diffSeconds);
  const suffix = diffSeconds >= 0 ? "前" : "後";

  const units: [number, string][] = [
    [60, "秒"],
    [60, "分"],
    [24, "時間"],
    [30, "日"],
    [12, "ヶ月"],
    [Number.POSITIVE_INFINITY, "年"],
  ];

  let value = abs;
  for (const [factor, label] of units) {
    if (value < factor) {
      return `${Math.max(1, Math.floor(value))}${label}${suffix}`;
    }
    value = Math.floor(value / factor);
  }
  return dateString;
}
