<?php

namespace App\Services\FinancialAnalysis;

use Illuminate\Support\Collection;

/**
 * Turns a per-stock series of fiscal-year FinancialStatement rows into
 * year-over-year growth, margins, and multi-year CAGR. Kept as a plain
 * service (not a model accessor) because it operates across a collection
 * of rows/stocks rather than a single record, and needs to be reusable
 * identically on the stock detail page, the theme comparison page, and
 * the SBI holdings dashboard.
 */
class FinancialTrendCalculator
{
    /**
     * @param  Collection  $statements  FinancialStatement rows for one stock,
     *                                  already filtered to period_type = 'FY'
     *                                  and ordered by fiscal_year ascending.
     */
    public function __construct(private readonly Collection $statements)
    {
    }

    /**
     * One row per fiscal year with raw metrics plus YoY growth and margins.
     */
    public function toTrendRows(): array
    {
        $rows = [];
        $previous = null;

        foreach ($this->statements as $statement) {
            $rows[] = [
                'fiscal_year' => $statement->fiscal_year,
                'net_sales' => $statement->net_sales,
                'operating_profit' => $statement->operating_profit,
                'ordinary_profit' => $statement->ordinary_profit,
                'profit' => $statement->profit,
                'eps' => $statement->eps,
                'roe' => $statement->roe,
                'equity_ratio' => $statement->equity_ratio,
                'yoy_net_sales' => $this->growthRate($previous?->net_sales, $statement->net_sales),
                'yoy_operating_profit' => $this->growthRate($previous?->operating_profit, $statement->operating_profit),
                'yoy_profit' => $this->growthRate($previous?->profit, $statement->profit),
                'operating_margin' => $this->ratio($statement->operating_profit, $statement->net_sales),
                'ordinary_margin' => $this->ratio($statement->ordinary_profit, $statement->net_sales),
                'net_margin' => $this->ratio($statement->profit, $statement->net_sales),
            ];

            $previous = $statement;
        }

        return $rows;
    }

    /**
     * Compound annual growth rate for a metric over the requested window
     * (or the longest window available if fewer data points exist).
     * Returns null when fewer than 2 usable data points exist.
     */
    public function cagr(string $metric, int $years = 3): ?float
    {
        $series = $this->statements
            ->map(fn ($s) => [$s->fiscal_year, $s->{$metric} ?? null])
            ->filter(fn ($pair) => $pair[1] !== null)
            ->values();

        if ($series->count() < 2) {
            return null;
        }

        $window = $series->slice(-($years + 1))->values();
        if ($window->count() < 2) {
            $window = $series;
        }

        [$startYear, $startValue] = $window->first();
        [$endYear, $endValue] = $window->last();

        $elapsedYears = $endYear - $startYear;

        if ($elapsedYears <= 0 || $startValue == 0 || ($startValue < 0) !== ($endValue < 0)) {
            return null;
        }

        return round((pow($endValue / $startValue, 1 / $elapsedYears) - 1) * 100, 2);
    }

    private function growthRate(?float $previous, ?float $current): ?float
    {
        if ($previous === null || $current === null || $previous == 0) {
            return null;
        }

        return round((($current - $previous) / abs($previous)) * 100, 2);
    }

    private function ratio(?float $numerator, ?float $denominator): ?float
    {
        if ($numerator === null || $denominator === null || $denominator == 0) {
            return null;
        }

        return round(($numerator / $denominator) * 100, 2);
    }
}
