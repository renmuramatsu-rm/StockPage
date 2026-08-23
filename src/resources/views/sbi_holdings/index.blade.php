@extends('layouts.app')

@section('title', 'SBI保有銘柄')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-slate-900">SBI保有銘柄</h1>
    <a href="{{ route('sbi-holdings.create') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">+ 保有銘柄を追加</a>
</div>

@if ($holdings->isEmpty())
    <p class="text-slate-500">保有銘柄が登録されていません。SBI証券の保有銘柄画面を見ながら、銘柄コード・株数・取得単価を入力してください。</p>
@else
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">
            <div class="text-xs text-slate-500">取得金額合計</div>
            <div class="text-xl font-bold font-mono mt-1 text-slate-900">{{ number_format($summary['cost']) }}円</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">
            <div class="text-xs text-slate-500">評価額合計</div>
            <div class="text-xl font-bold font-mono mt-1 text-slate-900">{{ $summary['value'] !== null ? number_format($summary['value']).'円' : '—' }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">
            <div class="text-xs text-slate-500">評価損益合計</div>
            <div class="text-xl font-bold font-mono mt-1 {{ ($summary['pl'] ?? 0) < 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                {{ $summary['pl'] !== null ? ($summary['pl'] > 0 ? '+' : '').number_format($summary['pl']).'円' : '—' }}
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 mb-8">
        <h2 class="font-semibold text-slate-800 mb-1">ポートフォリオ分析</h2>
        <p class="text-xs text-slate-400 mb-4">評価額（取得できない銘柄は取得金額で代用）に基づく機械的な参考情報であり、投資助言ではありません。</p>

        <div class="mb-4 flex flex-col gap-2">
            @foreach ($portfolio['suggestions'] as $suggestion)
                <div class="flex items-start gap-2 text-sm bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-3 py-2">
                    <span class="mt-0.5">💡</span>
                    <span>{{ $suggestion }}</span>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="text-xs text-slate-500 mb-2">銘柄別配分</div>
                <div class="flex flex-col gap-2">
                    @foreach ($portfolio['by_stock'] as $s)
                        <div>
                            <div class="flex justify-between text-xs text-slate-600 mb-0.5">
                                <span>{{ $s['name'] }}</span>
                                <span class="font-mono">{{ $s['share'] }}%</span>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $s['share'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-500 mb-2">テーマ別配分</div>
                <div class="flex flex-col gap-2">
                    @foreach ($portfolio['by_theme'] as $t)
                        <div>
                            <div class="flex justify-between text-xs text-slate-600 mb-0.5">
                                <span>{{ $t['name'] }}</span>
                                <span class="font-mono">{{ $t['share'] }}%</span>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-500 rounded-full" style="width: {{ $t['share'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="border border-slate-200 rounded-2xl shadow-sm bg-white p-4 mb-8 max-w-md">
        <x-trend-chart id="allocation-chart" :config="$allocationChartConfig" />
    </div>

    <p class="text-xs text-slate-400 mb-2">※現在値は無料プランのため最新から遅延したデータです（リアルタイムではありません）。各行の現在値にマウスを乗せると基準日が確認できます。</p>
    <div class="border border-slate-200 rounded-2xl shadow-sm bg-white overflow-x-auto">
        <table class="w-full text-sm border-collapse min-w-[900px]">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-left">
                    <th class="py-2.5 px-3 text-slate-500 font-medium">銘柄</th>
                    <th class="py-2.5 px-3 text-slate-500 font-medium">上場市場</th>
                    <th class="py-2.5 px-3 text-right text-slate-500 font-medium">株数</th>
                    <th class="py-2.5 px-3 text-right text-slate-500 font-medium">取得単価</th>
                    <th class="py-2.5 px-3 text-right text-slate-500 font-medium">取得金額</th>
                    <th class="py-2.5 px-3 text-right text-slate-500 font-medium">現在値</th>
                    <th class="py-2.5 px-3 text-right text-slate-500 font-medium">評価額</th>
                    <th class="py-2.5 px-3 text-right text-slate-500 font-medium">評価損益</th>
                    <th class="py-2.5 px-3 text-slate-500 font-medium">投資判断</th>
                    <th class="py-2.5 px-3 text-slate-500 font-medium">テーマ</th>
                    <th class="py-2.5 px-3"></th>
                </tr>
            </thead>
            <tbody class="font-mono">
                @foreach ($holdings as $row)
                    @php $holding = $row['holding']; @endphp
                    <tr class="border-b border-slate-100 last:border-0 odd:bg-white even:bg-slate-50/60 hover:bg-indigo-50/60 transition-colors">
                        <td class="py-2 px-3 font-sans">
                            <a class="text-indigo-600 hover:underline font-medium" href="{{ route('stocks.show', $holding->code) }}">
                                {{ $holding->stock?->stockName }}（{{ $holding->code }}）
                            </a>
                        </td>
                        <td class="py-2 px-3 font-sans text-slate-500">{{ $holding->stock?->market?->market ?? '—' }}</td>
                        <td class="py-2 px-3 text-right">{{ number_format($holding->shares) }}</td>
                        <td class="py-2 px-3 text-right">{{ number_format($holding->average_acquisition_price, 2) }}</td>
                        <td class="py-2 px-3 text-right">{{ number_format($holding->acquisition_cost) }}</td>
                        <td class="py-2 px-3 text-right" @if ($row['price_date']) title="{{ $row['price_date'] }}時点" @endif>
                            @if ($row['current_price'] !== null)
                                {{ number_format($row['current_price'], 1) }}
                                @if ($row['price_change'] !== null)
                                    <span class="block text-xs {{ $row['price_change'] > 0 ? 'text-rose-600' : ($row['price_change'] < 0 ? 'text-emerald-600' : 'text-slate-400') }}">
                                        {{ $row['price_change'] > 0 ? '+' : '' }}{{ $row['price_change_percent'] }}%
                                    </span>
                                @endif
                            @else
                                <a href="{{ route('stocks.show', $holding->code) }}" class="font-sans text-slate-400 hover:text-indigo-600 hover:underline text-xs">未同期（開くと取得）</a>
                            @endif
                        </td>
                        <td class="py-2 px-3 text-right">{{ $row['market_value'] !== null ? number_format($row['market_value']) : '—' }}</td>
                        <td class="py-2 px-3 text-right font-semibold {{ ($row['unrealized_pl'] ?? 0) < 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $row['unrealized_pl'] !== null ? number_format($row['unrealized_pl']) : '—' }}
                        </td>
                        <td class="py-2 px-3 font-sans"><x-score-badge :score="$holding->stock?->score" :show-points="false" /></td>
                        <td class="py-2 px-3 font-sans">
                            @foreach ($holding->stock?->themes ?? [] as $theme)
                                <span class="inline-block bg-slate-100 text-slate-600 rounded-full px-2 py-0.5 text-xs mr-1 whitespace-nowrap">{{ $theme->name }}</span>
                            @endforeach
                        </td>
                        <td class="py-2 px-3 text-right font-sans">
                            <a class="text-indigo-600 hover:underline" href="{{ route('sbi-holdings.edit', $holding) }}">編集</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
