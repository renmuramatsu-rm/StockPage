<?php

namespace App\Services\FinancialAnalysis;

/**
 * Rule-based portfolio-level analysis across SBI holdings: concentration by
 * stock/theme and a handful of mechanical diversification suggestions.
 * Like StockScorer, this is a transparent, rule-based screen — not
 * investment advice.
 */
class PortfolioAnalyzer
{
    private const STOCK_CONCENTRATION_THRESHOLD = 30.0;

    private const THEME_CONCENTRATION_THRESHOLD = 40.0;

    /**
     * @param  array<int, array{code: string, name: string, value: float, themes: string[], badge: ?string}>  $holdings
     */
    public function analyze(array $holdings): array
    {
        $totalValue = array_sum(array_column($holdings, 'value'));

        $byStock = [];
        $themeValues = [];
        $badgeCounts = [];

        foreach ($holdings as $h) {
            $byStock[] = [
                'code' => $h['code'],
                'name' => $h['name'],
                'value' => $h['value'],
                'share' => $this->share($h['value'], $totalValue),
            ];

            $themes = empty($h['themes']) ? ['未分類'] : $h['themes'];
            foreach ($themes as $theme) {
                $themeValues[$theme] = ($themeValues[$theme] ?? 0) + $h['value'];
            }

            $badge = $h['badge'] ?? '未評価';
            $badgeCounts[$badge] = ($badgeCounts[$badge] ?? 0) + 1;
        }

        usort($byStock, fn ($a, $b) => $b['value'] <=> $a['value']);

        $byTheme = [];
        foreach ($themeValues as $name => $value) {
            $byTheme[] = ['name' => $name, 'value' => $value, 'share' => $this->share($value, $totalValue)];
        }
        usort($byTheme, fn ($a, $b) => $b['value'] <=> $a['value']);

        return [
            'total_value' => $totalValue,
            'by_stock' => $byStock,
            'by_theme' => $byTheme,
            'badge_counts' => $badgeCounts,
            'suggestions' => $this->buildSuggestions($byStock, $byTheme, $badgeCounts, count($holdings)),
        ];
    }

    private function share(float $value, float $total): float
    {
        return $total > 0 ? round($value / $total * 100, 1) : 0.0;
    }

    /**
     * @return string[]
     */
    private function buildSuggestions(array $byStock, array $byTheme, array $badgeCounts, int $count): array
    {
        $suggestions = [];

        if (! empty($byStock) && $byStock[0]['share'] > self::STOCK_CONCENTRATION_THRESHOLD) {
            $suggestions[] = "「{$byStock[0]['name']}」が保有額の{$byStock[0]['share']}%を占めています。1銘柄への集中度が高いため、分散を検討してもよいかもしれません。";
        }

        if (! empty($byTheme) && $byTheme[0]['share'] > self::THEME_CONCENTRATION_THRESHOLD) {
            $suggestions[] = "テーマ「{$byTheme[0]['name']}」が保有額の{$byTheme[0]['share']}%を占めています。同じテーマへの集中度が高いため、他のテーマへの分散を検討してもよいかもしれません。";
        }

        if ($count >= 3 && count($byTheme) <= 1) {
            $suggestions[] = '保有銘柄がすべて同じテーマに属しています。異なるテーマの銘柄を組み入れることでリスク分散が期待できます。';
        }

        $weak = ($badgeCounts['割高・要注意'] ?? 0) + ($badgeCounts['非推奨'] ?? 0);
        if ($weak > 0) {
            $suggestions[] = "投資判断スコアが低い（割高・要注意 / 非推奨）銘柄が{$weak}件あります。各銘柄の詳細ページで内容を確認することをおすすめします。";
        }

        $unscored = $badgeCounts['未評価'] ?? 0;
        if ($unscored > 0) {
            $suggestions[] = "{$unscored}件の銘柄がまだ評価されていません。各銘柄の詳細ページを開くとスコアが計算されます。";
        }

        if (empty($suggestions)) {
            $suggestions[] = '現時点で特に大きな偏りは見られません。';
        }

        return $suggestions;
    }
}
