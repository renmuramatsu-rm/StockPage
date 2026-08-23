<?php

namespace App\Services\FinancialAnalysis;

/**
 * Builds a short "company overview" paragraph purely from data already on
 * hand (J-Quants listed-issue master + our own computed financial trend).
 * This is deliberately NOT scraped/copied from any news or 四季報-style
 * source — it's a templated summary of numbers we already store.
 */
class CompanyOverviewBuilder
{
    /**
     * @param  array{market: ?string, scale_category: ?string, themes: string[], latest: ?array}  $input
     *   'latest', when present, has keys: fiscal_year, net_sales, yoy_net_sales, operating_profit, roe (all nullable except fiscal_year).
     */
    public function build(string $name, array $input): string
    {
        $sentences = [];
        $sentences[] = $this->introSentence($name, $input);

        if (! empty($input['scale_category'])) {
            $sentences[] = "規模区分は「{$input['scale_category']}」です。";
        }

        if ($performance = $this->performanceSentence($input['latest'] ?? null)) {
            $sentences[] = $performance;
        }

        return implode('', array_filter($sentences));
    }

    private function introSentence(string $name, array $input): string
    {
        $parts = [];

        if (! empty($input['market'])) {
            $parts[] = "東証{$input['market']}市場に上場";
        }

        if (! empty($input['themes'])) {
            $parts[] = implode('・', $input['themes']).'に分類される';
        }

        if (empty($parts)) {
            return "{$name}の情報です。";
        }

        return "{$name}は".implode('し、', $parts)."企業です。";
    }

    private function performanceSentence(?array $latest): ?string
    {
        if (empty($latest) || $latest['net_sales'] === null) {
            return null;
        }

        $sales = number_format((int) round($latest['net_sales'] / 100_000_000));
        $sentence = "直近（{$latest['fiscal_year']}年度）の売上高は{$sales}億円";

        if ($latest['yoy_net_sales'] !== null) {
            $sentence .= "（前年比{$latest['yoy_net_sales']}%）";
        }

        if (! empty($latest['operating_profit'])) {
            $op = number_format((int) round($latest['operating_profit'] / 100_000_000));
            $sentence .= "、営業利益は{$op}億円";
        }

        if ($latest['roe'] !== null) {
            $sentence .= "、ROEは{$latest['roe']}%";
        }

        return $sentence.'です。';
    }
}
