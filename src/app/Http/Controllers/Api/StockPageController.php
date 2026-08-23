<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\Theme;
use App\Services\FinancialAnalysis\CompanyOverviewBuilder;
use App\Services\FinancialAnalysis\FinancialTrendCalculator;
use App\Services\FinancialAnalysis\StockScoreRecorder;
use App\Services\FinancialData\FinancialStatementSyncer;
use Illuminate\Http\Request;

class StockPageController extends Controller
{
    /**
     * Badges a stock's score can carry, in display order, matching
     * StockScorer's bands. Used to build the index page filter dropdown.
     */
    public const BADGES = ['買い候補', 'やや魅力的', '様子見', '割高・要注意', '非推奨'];

    public function index(Request $request)
    {
        $stocks = Stock::with(['market', 'themes', 'score'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->input('q');
                $q->where(fn ($w) => $w->where('code', 'like', "%{$term}%")->orWhere('stockName', 'like', "%{$term}%"));
            })
            ->when($request->filled('market_id'), fn ($q) => $q->where('market_id', $request->input('market_id')))
            ->when($request->filled('theme_id'), fn ($q) => $q->whereHas('themes', fn ($t) => $t->where('themes.id', $request->input('theme_id'))))
            ->when($request->filled('badge'), function ($q) use ($request) {
                if ($request->input('badge') === '未評価') {
                    $q->whereDoesntHave('score');
                } else {
                    $q->whereHas('score', fn ($s) => $s->where('badge', $request->input('badge')));
                }
            })
            ->orderBy('code')
            ->paginate(20)
            ->withQueryString();

        // Public page (guests included) — only system sector tags are
        // shown as filter options, never another user's private themes.
        $themes = Theme::whereNull('user_id')->orderBy('name')->get();

        return response()->json([
            'stocks' => $stocks,
            'themes' => $themes,
            'badges' => self::BADGES,
        ]);
    }

    public function show(Stock $stock, FinancialStatementSyncer $syncer, StockScoreRecorder $scoreRecorder)
    {
        $stock->load(['themes', 'market']);

        $syncError = null;

        if ($syncer->needsSync($stock)) {
            try {
                $syncer->sync($stock);
            } catch (\Throwable $e) {
                $syncError = '財務データの取得に失敗しました（'.$e->getMessage().'）。時間をおいて再度お試しください。';
            }
        }

        $statements = $stock->financialStatements()->fiscalYearOnly()->orderedByPeriod()->get();
        $calculator = new FinancialTrendCalculator($statements);
        $trendRows = $calculator->toTrendRows();

        $cagr = [
            'net_sales' => $calculator->cagr('net_sales'),
            'operating_profit' => $calculator->cagr('operating_profit'),
            'profit' => $calculator->cagr('profit'),
        ];

        $fiscalYears = array_column($trendRows, 'fiscal_year');
        $cagrYears = count($fiscalYears) >= 2 ? max($fiscalYears) - min($fiscalYears) : 0;

        $labels = $fiscalYears;
        $oku = fn (array $values) => array_map(fn ($v) => $v !== null ? round($v / 100_000_000, 1) : null, $values);

        $salesChartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => '売上高（億円）',
                        'data' => $oku(array_column($trendRows, 'net_sales')),
                        'backgroundColor' => '#60a5fa',
                        'borderRadius' => 6,
                        'maxBarThickness' => 72,
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'plugins' => ['title' => ['display' => true, 'text' => '売上高推移（億円）'], 'legend' => ['display' => false]],
                'scales' => ['y' => ['title' => ['display' => true, 'text' => '億円'], 'beginAtZero' => true]],
            ],
        ];

        $profitChartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    ['label' => '営業利益', 'data' => $oku(array_column($trendRows, 'operating_profit')), 'backgroundColor' => '#34d399', 'borderRadius' => 6, 'maxBarThickness' => 40],
                    ['label' => '経常利益', 'data' => $oku(array_column($trendRows, 'ordinary_profit')), 'backgroundColor' => '#a78bfa', 'borderRadius' => 6, 'maxBarThickness' => 40],
                    ['label' => '純利益', 'data' => $oku(array_column($trendRows, 'profit')), 'backgroundColor' => '#fbbf24', 'borderRadius' => 6, 'maxBarThickness' => 40],
                ],
            ],
            'options' => [
                'responsive' => true,
                'plugins' => ['title' => ['display' => true, 'text' => '利益推移（億円）']],
                'scales' => ['y' => ['title' => ['display' => true, 'text' => '億円'], 'beginAtZero' => true]],
            ],
        ];

        $ratioChartConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'ROE(%)',
                        'data' => array_column($trendRows, 'roe'),
                        'borderColor' => '#f87171',
                        'backgroundColor' => '#f87171',
                        'tension' => 0.3,
                        'pointRadius' => 4,
                        'pointHoverRadius' => 6,
                        'yAxisID' => 'yRoe',
                    ],
                    [
                        'label' => '自己資本比率(%)',
                        'data' => array_column($trendRows, 'equity_ratio'),
                        'borderColor' => '#38bdf8',
                        'backgroundColor' => '#38bdf8',
                        'tension' => 0.3,
                        'pointRadius' => 4,
                        'pointHoverRadius' => 6,
                        'yAxisID' => 'yEquity',
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'plugins' => ['title' => ['display' => true, 'text' => '財務指標推移（%）']],
                'scales' => [
                    'yRoe' => [
                        'position' => 'left',
                        'title' => ['display' => true, 'text' => 'ROE(%)', 'color' => '#f87171'],
                        'ticks' => ['color' => '#f87171'],
                    ],
                    'yEquity' => [
                        'position' => 'right',
                        'title' => ['display' => true, 'text' => '自己資本比率(%)', 'color' => '#38bdf8'],
                        'ticks' => ['color' => '#38bdf8'],
                        'grid' => ['drawOnChartArea' => false],
                    ],
                ],
            ],
        ];

        $scoreRecord = $scoreRecorder->record($stock);

        $latestRow = ! empty($trendRows) ? end($trendRows) : null;
        $overview = (new CompanyOverviewBuilder())->build($stock->stockName, [
            'market' => $stock->market?->market,
            'scale_category' => $stock->scale_category,
            'themes' => $stock->themes->pluck('name')->all(),
            'latest' => $latestRow,
        ]);

        return response()->json([
            'stock' => $stock,
            'trendRows' => $trendRows,
            'cagr' => $cagr,
            'cagrYears' => $cagrYears,
            'salesChartConfig' => $salesChartConfig,
            'profitChartConfig' => $profitChartConfig,
            'ratioChartConfig' => $ratioChartConfig,
            'syncError' => $syncError,
            'scoreRecord' => $scoreRecord,
            'overview' => $overview,
        ]);
    }

    public function editThemes(Stock $stock)
    {
        $stock->load('themes');
        $themes = Theme::where('user_id', auth()->id())->orderBy('name')->get();

        return response()->json([
            'stock' => $stock,
            'themes' => $themes,
        ]);
    }

    public function updateThemes(Request $request, Stock $stock)
    {
        // Only touch the user's own themes here — leave system sector
        // tags and other users' theme assignments on this stock alone.
        $ownThemeIds = Theme::where('user_id', auth()->id())->pluck('id');
        $selectedIds = collect($request->input('theme_ids', []))->map(fn ($id) => (int) $id)->intersect($ownThemeIds);

        $stock->themes()->detach($ownThemeIds->diff($selectedIds));
        $stock->themes()->syncWithoutDetaching($selectedIds);

        return response()->json(['stock' => $stock->load('themes')]);
    }
}
