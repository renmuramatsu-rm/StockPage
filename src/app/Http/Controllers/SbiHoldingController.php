<?php

namespace App\Http\Controllers;

use App\Http\Requests\SbiHoldingRequest;
use App\Models\SbiHolding;
use App\Models\Stock;
use App\Services\FinancialAnalysis\PortfolioAnalyzer;

class SbiHoldingController extends Controller
{
    /**
     * Prices are read from the persisted stock_scores table (kept fresh by
     * financials:sync / on-demand sync on the stock detail page) rather
     * than fetched live here. Live-fetching one call per holding on every
     * page view has no rate-limit throttling and would blow through the
     * free plan's 5 calls/min limit for portfolios with more than a
     * handful of holdings.
     */
    public function index(PortfolioAnalyzer $analyzer)
    {
        $holdings = SbiHolding::with(['stock.themes', 'stock.score', 'stock.market'])->get()->map(function (SbiHolding $holding) {
            $score = $holding->stock?->score;
            $price = $score?->current_price !== null ? (float) $score->current_price : null;
            $marketValue = $price !== null ? $price * $holding->shares : null;

            return [
                'holding' => $holding,
                'current_price' => $price,
                'price_date' => $score?->price_date?->format('Y-m-d'),
                'price_change' => $score?->price_change !== null ? (float) $score->price_change : null,
                'price_change_percent' => $score?->price_change_percent !== null ? (float) $score->price_change_percent : null,
                'market_value' => $marketValue,
                'unrealized_pl' => $marketValue !== null ? $marketValue - $holding->acquisition_cost : null,
            ];
        });

        $themeAllocation = [];
        foreach ($holdings as $row) {
            $themes = $row['holding']->stock?->themes ?? collect();
            $themeNames = $themes->isEmpty() ? ['未分類'] : $themes->pluck('name')->all();

            foreach ($themeNames as $name) {
                $themeAllocation[$name] = ($themeAllocation[$name] ?? 0) + $row['holding']->acquisition_cost;
            }
        }

        $allocationChartConfig = [
            'type' => 'doughnut',
            'data' => [
                'labels' => array_keys($themeAllocation),
                'datasets' => [[
                    'label' => 'テーマ別配分（取得金額）',
                    'data' => array_values($themeAllocation),
                ]],
            ],
            'options' => [
                'responsive' => true,
                'plugins' => ['title' => ['display' => true, 'text' => 'テーマ別配分（取得金額）']],
            ],
        ];

        $summary = [
            'cost' => $holdings->sum(fn ($row) => $row['holding']->acquisition_cost),
            'value' => $holdings->sum('market_value'),
            'pl' => $holdings->sum('unrealized_pl'),
        ];

        $portfolio = $analyzer->analyze($holdings->map(function ($row) {
            $holding = $row['holding'];

            return [
                'code' => $holding->code,
                'name' => $holding->stock?->stockName ?? $holding->code,
                'value' => $row['market_value'] ?? $holding->acquisition_cost,
                'themes' => $holding->stock?->themes->pluck('name')->all() ?? [],
                'badge' => $holding->stock?->score?->badge,
            ];
        })->values()->all());

        return view('sbi_holdings.index', compact('holdings', 'allocationChartConfig', 'summary', 'portfolio'));
    }

    public function create()
    {
        $stocks = Stock::orderBy('code')->get();

        return view('sbi_holdings.create', compact('stocks'));
    }

    public function store(SbiHoldingRequest $request)
    {
        SbiHolding::create($request->validated());

        return redirect()->route('sbi-holdings.index');
    }

    public function edit(SbiHolding $sbiHolding)
    {
        $stocks = Stock::orderBy('code')->get();

        return view('sbi_holdings.edit', ['holding' => $sbiHolding, 'stocks' => $stocks]);
    }

    public function update(SbiHoldingRequest $request, SbiHolding $sbiHolding)
    {
        $sbiHolding->update($request->validated());

        return redirect()->route('sbi-holdings.index');
    }

    public function destroy(SbiHolding $sbiHolding)
    {
        $sbiHolding->delete();

        return redirect()->route('sbi-holdings.index');
    }
}
