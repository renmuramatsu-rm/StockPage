@extends('layouts.app')

@section('title', $stock->stockName . ' | 銘柄詳細')

@section('content')
<div class="mb-3">
    <a href="{{ route('stocks.index') }}" class="text-sm text-slate-500 hover:text-indigo-600">&larr; 銘柄一覧へ</a>
</div>

<div class="mb-6 flex items-start justify-between flex-wrap gap-2">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ $stock->stockName }} <span class="text-slate-400 font-mono text-lg">{{ $stock->code }}</span></h1>
        <p class="text-slate-500 text-sm mt-0.5">{{ $stock->market?->market }}</p>
    </div>
    @auth
        <a href="{{ route('stocks.themes.edit', $stock) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">テーマを編集</a>
    @endauth
</div>

<div class="mb-6 flex flex-wrap gap-2">
    @forelse ($stock->themes as $theme)
        <span class="inline-block bg-slate-100 text-slate-600 rounded-full px-3 py-1 text-xs">{{ $theme->name }}</span>
    @empty
        <span class="text-slate-400 text-sm">テーマ未設定</span>
    @endforelse
</div>

@if ($syncError)
    <p class="text-sm text-red-700 mb-6 bg-red-50 border border-red-200 rounded-lg p-3">{{ $syncError }}</p>
@endif

<div class="border border-slate-200 rounded-2xl shadow-sm p-5 mb-6 bg-white">
    <h2 class="text-sm font-semibold text-slate-700 mb-2">会社概要</h2>
    <p class="text-sm text-slate-600 leading-relaxed mb-4">{{ $overview }}</p>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm border-t border-slate-100 pt-4">
        <div>
            <div class="text-xs text-slate-400">銘柄コード</div>
            <div class="font-mono font-medium text-slate-800">{{ $stock->code }}</div>
        </div>
        <div>
            <div class="text-xs text-slate-400">上場市場</div>
            <div class="font-medium text-slate-800">{{ $stock->market?->market ?? '—' }}</div>
        </div>
        <div>
            <div class="text-xs text-slate-400">規模区分</div>
            <div class="font-medium text-slate-800">{{ $stock->scale_category ?? '—' }}</div>
        </div>
        <div>
            <div class="text-xs text-slate-400">テーマ</div>
            <div class="font-medium text-slate-800">{{ $stock->themes->isNotEmpty() ? $stock->themes->pluck('name')->join('、') : '—' }}</div>
        </div>
    </div>
</div>

<div class="border border-slate-200 rounded-2xl shadow-sm p-5 mb-6 bg-white">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <div class="text-xs text-slate-500 mb-1.5">投資判断スコア（機械的な参考指標であり、投資助言ではありません）</div>
            <x-score-badge :score="$scoreRecord" class="text-sm px-3 py-1" />
        </div>
        <div class="text-sm text-right">
            @if ($scoreRecord->current_price !== null)
                <div>
                    <span class="text-xl font-bold text-slate-900 font-mono">{{ number_format($scoreRecord->current_price, 1) }}</span>
                    <span class="text-slate-400 text-xs">円</span>
                    @if ($scoreRecord->price_change !== null)
                        <span class="ml-1.5 font-semibold {{ $scoreRecord->price_change > 0 ? 'text-rose-600' : ($scoreRecord->price_change < 0 ? 'text-emerald-600' : 'text-slate-500') }}">
                            {{ $scoreRecord->price_change > 0 ? '+' : '' }}{{ $scoreRecord->price_change }}
                            ({{ $scoreRecord->price_change > 0 ? '+' : '' }}{{ $scoreRecord->price_change_percent }}%)
                        </span>
                    @endif
                </div>
                <div class="text-slate-400 text-xs mt-0.5">
                    {{ $scoreRecord->price_date?->format('Y/m/d') }}時点
                    @if ($scoreRecord->per !== null)<span class="ml-2">PER {{ $scoreRecord->per }}倍</span>@endif
                    @if ($scoreRecord->pbr !== null)<span class="ml-2">PBR {{ $scoreRecord->pbr }}倍</span>@endif
                </div>
                <p class="text-[11px] text-slate-400 mt-1">※無料プランのため株価は最新から遅延したデータです（リアルタイムではありません）</p>
            @else
                <span class="text-slate-400">株価取得失敗</span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4 text-sm">
        <div class="bg-slate-50 rounded-xl p-3.5">
            <div class="text-slate-500 text-xs">成長性</div>
            <div class="font-semibold text-slate-800 mt-0.5">{{ $scoreRecord->growth_label }}{{ $scoreRecord->growth_score !== null ? '（'.$scoreRecord->growth_score.'点）' : '' }}</div>
        </div>
        <div class="bg-slate-50 rounded-xl p-3.5">
            <div class="text-slate-500 text-xs">割安性（PER・PBR）</div>
            <div class="font-semibold text-slate-800 mt-0.5">{{ $scoreRecord->valuation_label }}{{ $scoreRecord->valuation_score !== null ? '（'.$scoreRecord->valuation_score.'点）' : '' }}</div>
        </div>
        <div class="bg-slate-50 rounded-xl p-3.5">
            <div class="text-slate-500 text-xs">収益性・財務健全性</div>
            <div class="font-semibold text-slate-800 mt-0.5">{{ $scoreRecord->quality_label }}{{ $scoreRecord->quality_score !== null ? '（'.$scoreRecord->quality_score.'点）' : '' }}</div>
        </div>
    </div>
</div>

@php
    $q = urlencode($stock->stockName);
@endphp
<div class="mb-6">
    <div class="text-xs text-slate-500 mb-2">最新トピック・関連ニュース（外部サイトを開きます。著作権の関係でこのサイト内に記事は表示していません）</div>
    <div class="flex flex-wrap gap-2 text-sm">
        <a href="https://finance.yahoo.co.jp/quote/{{ $stock->code }}.T/disclosure" target="_blank" rel="noopener" class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition font-medium">適時開示情報（決算・IR発表）</a>
        <a href="https://finance.yahoo.co.jp/quote/{{ $stock->code }}.T/news" target="_blank" rel="noopener" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition">Yahoo!ファイナンスのニュース</a>
        <a href="https://www.google.com/search?q=site:shikiho.toyokeizai.net+{{ $q }}" target="_blank" rel="noopener" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition">会社四季報オンラインで検索</a>
        <a href="https://www.google.com/search?q=site:nikkei.com+{{ $q }}+株価" target="_blank" rel="noopener" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition">日経電子版で検索</a>
        <a href="https://www.google.com/search?q={{ $q }}+{{ $stock->code }}&tbm=nws" target="_blank" rel="noopener" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition">Googleニュースで検索</a>
    </div>
</div>

@if (empty($trendRows))
    <p class="text-slate-500 mb-6">財務データがまだありません。<code class="bg-slate-100 px-1.5 py-0.5 rounded text-xs">php artisan financials:sync {{ $stock->code }}</code> を実行するか、時間をおいてこのページを開き直してください。</p>
@else
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
        <div class="border border-slate-200 rounded-2xl shadow-sm p-4 bg-white">
            <div class="text-sm text-slate-500">売上高 CAGR（過去{{ $cagrYears }}年）</div>
            <div class="text-2xl font-bold font-mono mt-0.5 {{ ($cagr['net_sales'] ?? 0) < 0 ? 'text-rose-600' : 'text-slate-900' }}">{{ $cagr['net_sales'] !== null ? $cagr['net_sales'].'%' : '—' }}</div>
        </div>
        <div class="border border-slate-200 rounded-2xl shadow-sm p-4 bg-white">
            <div class="text-sm text-slate-500">営業利益 CAGR（過去{{ $cagrYears }}年）</div>
            <div class="text-2xl font-bold font-mono mt-0.5 {{ ($cagr['operating_profit'] ?? 0) < 0 ? 'text-rose-600' : 'text-slate-900' }}">{{ $cagr['operating_profit'] !== null ? $cagr['operating_profit'].'%' : '—' }}</div>
        </div>
        <div class="border border-slate-200 rounded-2xl shadow-sm p-4 bg-white">
            <div class="text-sm text-slate-500">純利益 CAGR（過去{{ $cagrYears }}年）</div>
            <div class="text-2xl font-bold font-mono mt-0.5 {{ ($cagr['profit'] ?? 0) < 0 ? 'text-rose-600' : 'text-slate-900' }}">{{ $cagr['profit'] !== null ? $cagr['profit'].'%' : '—' }}</div>
        </div>
    </div>

    @if ($cagrYears < 3)
        <p class="text-xs text-amber-600 mb-6">※現在のJ-Quantsプランでは過去{{ $cagrYears }}年分のデータしか取得できていないため、参考値です。3年以上の推移を見るにはJ-Quantsの有料プラン（Light以上）への切り替えが必要です。</p>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="border border-slate-200 rounded-2xl shadow-sm p-4 bg-white">
            <x-trend-chart id="sales-chart" :config="$salesChartConfig" />
        </div>
        <div class="border border-slate-200 rounded-2xl shadow-sm p-4 bg-white">
            <x-trend-chart id="profit-chart" :config="$profitChartConfig" />
        </div>
        <div class="border border-slate-200 rounded-2xl shadow-sm p-4 bg-white lg:col-span-2">
            <x-trend-chart id="ratio-chart" :config="$ratioChartConfig" height="120" />
        </div>
    </div>

    <div class="border border-slate-200 rounded-2xl shadow-sm bg-white overflow-x-auto">
        <table class="w-full text-sm border-collapse min-w-[720px]">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-left">
                    <th class="py-2.5 px-3 text-slate-500 font-medium">年度</th>
                    <th class="py-2.5 px-3 text-right text-slate-500 font-medium">売上高</th>
                    <th class="py-2.5 px-3 text-right text-slate-500 font-medium">前年比</th>
                    <th class="py-2.5 px-3 text-right text-slate-500 font-medium">営業利益</th>
                    <th class="py-2.5 px-3 text-right text-slate-500 font-medium">営業利益率</th>
                    <th class="py-2.5 px-3 text-right text-slate-500 font-medium">純利益</th>
                    <th class="py-2.5 px-3 text-right text-slate-500 font-medium">EPS</th>
                    <th class="py-2.5 px-3 text-right text-slate-500 font-medium">ROE</th>
                    <th class="py-2.5 px-3 text-right text-slate-500 font-medium">自己資本比率</th>
                </tr>
            </thead>
            <tbody class="font-mono">
                @foreach ($trendRows as $row)
                    <tr class="border-b border-slate-100 last:border-0 odd:bg-white even:bg-slate-50/60 hover:bg-indigo-50/60 transition-colors">
                        <td class="py-2 px-3 font-sans font-medium text-slate-800">{{ $row['fiscal_year'] }}</td>
                        <td class="py-2 px-3 text-right">{{ $row['net_sales'] !== null ? number_format($row['net_sales']) : '—' }}</td>
                        <td class="py-2 px-3 text-right {{ ($row['yoy_net_sales'] ?? 0) < 0 ? 'text-rose-600' : 'text-slate-600' }}">{{ $row['yoy_net_sales'] !== null ? $row['yoy_net_sales'].'%' : '—' }}</td>
                        <td class="py-2 px-3 text-right">{{ $row['operating_profit'] !== null ? number_format($row['operating_profit']) : '—' }}</td>
                        <td class="py-2 px-3 text-right text-slate-600">{{ $row['operating_margin'] !== null ? $row['operating_margin'].'%' : '—' }}</td>
                        <td class="py-2 px-3 text-right">{{ $row['profit'] !== null ? number_format($row['profit']) : '—' }}</td>
                        <td class="py-2 px-3 text-right text-slate-600">{{ $row['eps'] ?? '—' }}</td>
                        <td class="py-2 px-3 text-right text-slate-600">{{ $row['roe'] !== null ? $row['roe'].'%' : '—' }}</td>
                        <td class="py-2 px-3 text-right text-slate-600">{{ $row['equity_ratio'] !== null ? $row['equity_ratio'].'%' : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
