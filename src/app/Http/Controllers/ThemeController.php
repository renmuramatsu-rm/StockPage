<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThemeRequest;
use App\Models\Stock;
use App\Models\StockScore;
use App\Models\Theme;
use App\Services\FinancialAnalysis\FinancialTrendCalculator;
use Illuminate\Support\Facades\Http;

class ThemeController extends Controller
{
    /**
     * A theme (especially an auto-tagged 17-sector one) can hold well over
     * 100 stocks; charting one line per stock beyond this point stops being
     * readable, so the comparison chart is capped to the largest movers.
     */
    private const MAX_CHART_SERIES = 15;

    public function index()
    {
        $themes = Theme::withCount('stocks')->orderByDesc('stocks_count')->orderBy('name')->get();

        $nikkei = $this->fetchNikkei();

        $lastSynced = StockScore::max('computed_at');

        $stats = [
            'stocks' => Stock::count(),
            'scored' => StockScore::count(),
            'buy_candidates' => StockScore::where('badge', '買い候補')->count(),
            'last_synced' => $lastSynced ? \Carbon\Carbon::parse($lastSynced) : null,
        ];

        return view('themes.index', compact('themes', 'nikkei', 'stats'));
    }

    public function show(Theme $theme)
    {
        $theme->load([
            'stocks' => fn ($q) => $q->orderBy('code'),
            'stocks.score',
            'stocks.financialStatements' => function ($query) {
                $query->fiscalYearOnly()->orderedByPeriod();
            },
        ]);

        $labels = [];
        $series = [];

        foreach ($theme->stocks as $stock) {
            $rows = (new FinancialTrendCalculator($stock->financialStatements))->toTrendRows();

            if (empty($rows)) {
                continue;
            }

            $labels = array_unique(array_merge($labels, array_column($rows, 'fiscal_year')));
            $latestNetSales = end($rows)['net_sales'] ?? 0;
            $series[] = [
                'label' => $stock->stockName,
                'data' => array_map(fn ($v) => $v !== null ? round($v / 100_000_000, 1) : null, array_column($rows, 'net_sales')),
                'latest_net_sales' => $latestNetSales,
            ];
        }

        sort($labels);

        $truncated = count($series) > self::MAX_CHART_SERIES;
        usort($series, fn ($a, $b) => $b['latest_net_sales'] <=> $a['latest_net_sales']);
        $datasets = array_map(
            fn ($s) => ['label' => $s['label'], 'data' => $s['data'], 'fill' => false, 'tension' => 0.2],
            array_slice($series, 0, self::MAX_CHART_SERIES)
        );

        $chartConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => $datasets,
            ],
            'options' => [
                'responsive' => true,
                'scales' => ['y' => ['title' => ['display' => true, 'text' => '億円']]],
                'plugins' => ['title' => ['display' => true, 'text' => '売上高推移（億円）'.($truncated ? '（売上高上位'.self::MAX_CHART_SERIES.'社）' : '')]],
            ],
        ];

        return view('themes.show', compact('theme', 'chartConfig', 'truncated'));
    }

    public function create()
    {
        return view('themes.create');
    }

    public function store(ThemeRequest $request)
    {
        Theme::create($request->validated());

        return redirect()->route('themes.dashboard');
    }

    public function edit(Theme $theme)
    {
        return view('themes.edit', compact('theme'));
    }

    public function update(ThemeRequest $request, Theme $theme)
    {
        $theme->update($request->validated());

        return redirect()->route('themes.dashboard');
    }

    public function destroy(Theme $theme)
    {
        $theme->delete();

        return redirect()->route('themes.dashboard');
    }

    private function fetchNikkei(): string
    {
        $apiKey = config('services.twelvedata.key');

        if (! $apiKey) {
            return '未設定';
        }

        try {
            $response = Http::get('https://api.twelvedata.com/price', [
                'symbol' => 'N225',
                'apikey' => $apiKey,
            ]);

            return $response->json('price') ?? '取得失敗';
        } catch (\Throwable $e) {
            return '取得失敗';
        }
    }
}
