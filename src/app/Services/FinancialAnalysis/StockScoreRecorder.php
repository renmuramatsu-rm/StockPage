<?php

namespace App\Services\FinancialAnalysis;

use App\Models\Stock;
use App\Models\StockScore;
use App\Services\FinancialData\Contracts\FinancialStatementProviderInterface;
use Illuminate\Support\Facades\Cache;

/**
 * Computes a stock's "should I buy this?" score (see StockScorer) and
 * persists it to stock_scores, so the stock list page can filter/display
 * badges without recomputing them (or making a live price call) for every
 * row. Called both from the stock detail page (on-demand, one stock) and
 * from financials:sync (bulk backfill across many stocks).
 */
class StockScoreRecorder
{
    public function __construct(private readonly FinancialStatementProviderInterface $provider)
    {
    }

    public function record(Stock $stock): StockScore
    {
        $statements = $stock->financialStatements()->fiscalYearOnly()->orderedByPeriod()->get();
        $calculator = new FinancialTrendCalculator($statements);
        $latest = $statements->last();
        $trendRows = $calculator->toTrendRows();

        $quote = Cache::remember(
            "quote:{$stock->code}:".now()->toDateString(),
            now()->addHours(6),
            fn () => $this->provider->fetchLatestQuote((string) $stock->code)
        );

        $currentPrice = $quote['close'] ?? null;

        $per = ($currentPrice !== null && $latest?->eps > 0) ? round($currentPrice / $latest->eps, 2) : null;
        $pbr = ($currentPrice !== null && $latest?->bps > 0) ? round($currentPrice / $latest->bps, 2) : null;

        $score = (new StockScorer())->score([
            'sales_growth' => $calculator->cagr('net_sales') ?? ($trendRows ? end($trendRows)['yoy_net_sales'] : null),
            'profit_growth' => $calculator->cagr('profit') ?? ($trendRows ? end($trendRows)['yoy_profit'] : null),
            'per' => $per,
            'pbr' => $pbr,
            'roe' => $latest?->roe !== null ? (float) $latest->roe : null,
            'equity_ratio' => $latest?->equity_ratio !== null ? (float) $latest->equity_ratio : null,
            'roa' => $trendRows ? end($trendRows)['roa'] : null,
        ]);

        return StockScore::updateOrCreate(
            ['code' => $stock->code],
            [
                'overall_score' => $score['overall']['score'],
                'badge' => $score['overall']['badge'],
                'badge_color' => $score['overall']['color'],
                'growth_score' => $score['growth']['score'],
                'growth_label' => $score['growth']['label'],
                'valuation_score' => $score['valuation']['score'],
                'valuation_label' => $score['valuation']['label'],
                'quality_score' => $score['quality']['score'],
                'quality_label' => $score['quality']['label'],
                'current_price' => $currentPrice,
                'price_date' => $quote['date'] ?? null,
                'price_change' => $quote['change'] ?? null,
                'price_change_percent' => $quote['change_percent'] ?? null,
                'per' => $per,
                'pbr' => $pbr,
                'computed_at' => now(),
            ]
        );
    }
}
