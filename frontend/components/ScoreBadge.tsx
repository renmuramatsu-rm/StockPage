import { StockScore } from "@/lib/api";

const BADGE_CLASS_MAP: Record<string, string> = {
  green: "bg-emerald-50 text-emerald-700 border border-emerald-200",
  teal: "bg-teal-50 text-teal-700 border border-teal-200",
  amber: "bg-amber-50 text-amber-700 border border-amber-200",
  orange: "bg-orange-50 text-orange-700 border border-orange-200",
  red: "bg-rose-50 text-rose-700 border border-rose-200",
  gray: "bg-slate-100 text-slate-500 border border-slate-200",
};

export default function ScoreBadge({
  score,
  showPoints = true,
  className = "",
}: {
  score?: StockScore | null;
  showPoints?: boolean;
  className?: string;
}) {
  const badge = score?.badge ?? "データ不足";
  const badgeClass = BADGE_CLASS_MAP[score?.badge_color ?? "gray"] ?? BADGE_CLASS_MAP.gray;

  return (
    <span
      className={`inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap ${badgeClass} ${className}`}
    >
      {badge}
      {showPoints && score?.overall_score !== null && score?.overall_score !== undefined
        ? `（${score.overall_score}点）`
        : ""}
    </span>
  );
}
