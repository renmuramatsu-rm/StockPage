@extends('layouts.app')

@section('title', $theme->name . ' | テーマ')

@section('content')
<div class="mb-3">
    <a href="{{ route('themes.dashboard') }}" class="text-sm text-slate-500 hover:text-indigo-600">&larr; ダッシュボードへ</a>
</div>

<div class="mb-6 flex items-start justify-between flex-wrap gap-2">
    <div>
        <div class="flex items-center gap-2">
            <span class="inline-block w-3 h-3 rounded-full" style="background-color: {{ $theme->color ?? '#94a3b8' }}"></span>
            <h1 class="text-2xl font-bold text-slate-900">{{ $theme->name }}</h1>
        </div>
        @if ($theme->description)
            <p class="text-slate-500 text-sm mt-1">{{ $theme->description }}</p>
        @endif
        <p class="text-sm text-slate-400 mt-1 font-mono">{{ $theme->stocks->count() }} 銘柄</p>
    </div>
    @auth
        <a href="{{ route('themes.edit', $theme) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">編集</a>
    @endauth
</div>

<div class="border border-slate-200 rounded-2xl shadow-sm bg-white p-4 mb-8">
    <x-trend-chart id="theme-chart" :config="$chartConfig" />
    @if ($truncated)
        <p class="text-xs text-slate-400 mt-2">※銘柄数が多いため、グラフは売上高上位の銘柄のみ表示しています。下の一覧には全銘柄を掲載しています。</p>
    @endif
</div>

<div class="border border-slate-200 rounded-2xl shadow-sm bg-white overflow-x-auto">
    <table class="w-full text-sm border-collapse min-w-[560px]">
        <thead>
            <tr class="border-b border-slate-200 bg-slate-50 text-left">
                <th class="py-2.5 px-3 text-slate-500 font-medium">銘柄コード</th>
                <th class="py-2.5 px-3 text-slate-500 font-medium">銘柄名</th>
                <th class="py-2.5 px-3 text-slate-500 font-medium">市場</th>
                <th class="py-2.5 px-3 text-slate-500 font-medium">投資判断</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($theme->stocks as $stock)
                <tr class="border-b border-slate-100 last:border-0 odd:bg-white even:bg-slate-50/60 hover:bg-indigo-50/60 transition-colors">
                    <td class="py-2 px-3 font-mono"><a class="text-indigo-600 hover:underline font-medium" href="{{ route('stocks.show', $stock) }}">{{ $stock->code }}</a></td>
                    <td class="py-2 px-3"><a class="hover:underline hover:text-indigo-700" href="{{ route('stocks.show', $stock) }}">{{ $stock->stockName }}</a></td>
                    <td class="py-2 px-3 text-slate-500">{{ $stock->market?->market }}</td>
                    <td class="py-2 px-3"><x-score-badge :score="$stock->score" /></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
