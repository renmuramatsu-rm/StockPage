<?php

namespace App\Services\FinancialAnalysis;

/**
 * Rule-based "should I buy this?" screen across three axes the user chose:
 * growth, valuation (PER/PBR), and profitability/financial health
 * (ROE/equity ratio). This is a mechanical score derived from disclosed
 * figures only — not investment advice, just a way to triage many stocks
 * using the same criteria every time. Thresholds are simplified,
 * unadjusted-for-sector heuristics, documented inline.
 */
class StockScorer
{
    /**
     * @param  array{sales_growth: ?float, profit_growth: ?float, per: ?float, pbr: ?float, roe: ?float, equity_ratio: ?float, roa: ?float}  $inputs  Growth figures as percentages (e.g. 12.5 for +12.5%); ROE/ROA/equity_ratio as percentages.
     */
    public function score(array $inputs): array
    {
        $growth = $this->growthAxis($inputs['sales_growth'] ?? null, $inputs['profit_growth'] ?? null);
        $valuation = $this->valuationAxis($inputs['per'] ?? null, $inputs['pbr'] ?? null);
        $quality = $this->qualityAxis($inputs['roe'] ?? null, $inputs['equity_ratio'] ?? null, $inputs['roa'] ?? null);

        return [
            'growth' => $growth,
            'valuation' => $valuation,
            'quality' => $quality,
            'overall' => $this->overall([$growth['score'], $valuation['score'], $quality['score']]),
        ];
    }

    private function growthAxis(?float $salesGrowth, ?float $profitGrowth): array
    {
        $values = array_filter([$salesGrowth, $profitGrowth], fn ($v) => $v !== null);

        if (empty($values)) {
            return ['score' => null, 'label' => 'データ不足'];
        }

        $avg = array_sum($values) / count($values);

        return match (true) {
            $avg >= 15 => ['score' => 100, 'label' => '高成長'],
            $avg >= 5 => ['score' => 75, 'label' => '成長'],
            $avg >= 0 => ['score' => 50, 'label' => '低成長'],
            default => ['score' => 25, 'label' => '減収減益'],
        };
    }

    private function valuationAxis(?float $per, ?float $pbr): array
    {
        $perScore = match (true) {
            $per === null => null,
            $per <= 10 => 100,
            $per <= 15 => 75,
            $per <= 20 => 50,
            $per <= 30 => 25,
            default => 0,
        };

        $pbrScore = match (true) {
            $pbr === null => null,
            $pbr <= 0.8 => 100,
            $pbr <= 1.2 => 75,
            $pbr <= 2.0 => 50,
            $pbr <= 3.0 => 25,
            default => 0,
        };

        $scores = array_filter([$perScore, $pbrScore], fn ($v) => $v !== null);

        if (empty($scores)) {
            return ['score' => null, 'label' => 'データ不足'];
        }

        $score = (int) round(array_sum($scores) / count($scores));

        return ['score' => $score, 'label' => $this->band($score, ['割安', 'やや割安', '適正', 'やや割高', '割高'])];
    }

    private function qualityAxis(?float $roe, ?float $equityRatio, ?float $roa = null): array
    {
        $roeScore = match (true) {
            $roe === null => null,
            $roe >= 15 => 100,
            $roe >= 10 => 75,
            $roe >= 5 => 50,
            $roe >= 0 => 25,
            default => 0,
        };

        $equityScore = match (true) {
            $equityRatio === null => null,
            $equityRatio >= 50 => 100,
            $equityRatio >= 40 => 75,
            $equityRatio >= 25 => 50,
            $equityRatio >= 15 => 25,
            default => 0,
        };

        // ROA thresholds run roughly half of ROE's, since ROA isn't
        // inflated by leverage the way ROE can be — a highly-leveraged
        // company can post a strong ROE on a mediocre ROA.
        $roaScore = match (true) {
            $roa === null => null,
            $roa >= 10 => 100,
            $roa >= 5 => 75,
            $roa >= 2 => 50,
            $roa >= 0 => 25,
            default => 0,
        };

        $scores = array_filter([$roeScore, $equityScore, $roaScore], fn ($v) => $v !== null);

        if (empty($scores)) {
            return ['score' => null, 'label' => 'データ不足'];
        }

        $score = (int) round(array_sum($scores) / count($scores));

        return ['score' => $score, 'label' => $this->band($score, ['高収益・健全', '良好', '普通', 'やや弱い', '弱い'])];
    }

    private function overall(array $axisScores): array
    {
        $scores = array_filter($axisScores, fn ($v) => $v !== null);

        if (empty($scores)) {
            return ['score' => null, 'badge' => 'データ不足', 'color' => 'gray'];
        }

        $score = (int) round(array_sum($scores) / count($scores));

        return match (true) {
            $score >= 80 => ['score' => $score, 'badge' => '買い候補', 'color' => 'green'],
            $score >= 60 => ['score' => $score, 'badge' => 'やや魅力的', 'color' => 'teal'],
            $score >= 40 => ['score' => $score, 'badge' => '様子見', 'color' => 'amber'],
            $score >= 20 => ['score' => $score, 'badge' => '割高・要注意', 'color' => 'orange'],
            default => ['score' => $score, 'badge' => '非推奨', 'color' => 'red'],
        };
    }

    /**
     * Maps a 0-100 score onto one of 5 bands using the same cut points as
     * the overall badge (80/60/40/20), for per-axis labels.
     */
    private function band(int $score, array $labels): string
    {
        return match (true) {
            $score >= 80 => $labels[0],
            $score >= 60 => $labels[1],
            $score >= 40 => $labels[2],
            $score >= 20 => $labels[3],
            default => $labels[4],
        };
    }
}
