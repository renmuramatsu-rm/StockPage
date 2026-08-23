@extends('layouts.app')

@section('title', '銘柄一覧')

@section('content')
<div class="flex items-center justify-between mb-6 flex-wrap gap-2">
    <h1 class="text-2xl font-bold text-slate-900">銘柄一覧</h1>
    <p class="text-sm text-slate-500 font-mono">{{ $stocks->total() }} 件</p>
</div>

<form method="GET" class="mb-6 bg-white border border-slate-200 rounded-xl shadow-sm p-4 flex flex-wrap gap-4 items-end">
    <div class="flex-1 min-w-[220px]">
        <label class="block text-sm font-medium mb-1 text-slate-700" for="q">銘柄コード・銘柄名で検索</label>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
            <input type="text" name="q" id="q" value="{{ request('q') }}" placeholder="例: 7203 またはトヨタ"
                   class="border border-slate-300 rounded-lg pl-9 pr-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1 text-slate-700" for="theme_id">テーマ</label>
        <select name="theme_id" id="theme_id" class="border border-slate-300 rounded-lg px-3 py-2 bg-white">
            <option value="">すべて</option>
            @foreach ($themes as $theme)
                <option value="{{ $theme->id }}" @selected(request('theme_id') == $theme->id)>{{ $theme->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1 text-slate-700" for="badge">投資判断</label>
        <select name="badge" id="badge" class="border border-slate-300 rounded-lg px-3 py-2 bg-white">
            <option value="">すべて</option>
            @foreach ($badges as $badge)
                <option value="{{ $badge }}" @selected(request('badge') === $badge)>{{ $badge }}</option>
            @endforeach
            <option value="未評価" @selected(request('badge') === '未評価')>未評価（まだ見ていない銘柄）</option>
        </select>
    </div>
    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">絞り込む</button>
    @if (request('q') || request('theme_id') || request('badge'))
        <a href="{{ route('stocks.index') }}" class="text-sm text-slate-500 hover:text-indigo-600">解除</a>
    @endif
</form>

<div class="border border-slate-200 rounded-xl shadow-sm bg-white overflow-x-auto">
    <table class="w-full text-sm border-collapse min-w-[720px]">
        <thead>
            <tr class="border-b border-slate-200 bg-slate-50 text-left">
                <th class="py-2.5 px-3 text-slate-500 font-medium">コード</th>
                <th class="py-2.5 px-3 text-slate-500 font-medium">銘柄名</th>
                <th class="py-2.5 px-3 text-slate-500 font-medium">市場</th>
                <th class="py-2.5 px-3 text-slate-500 font-medium">投資判断</th>
                <th class="py-2.5 px-3 text-slate-500 font-medium">テーマ</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($stocks as $stock)
                <tr class="border-b border-slate-100 last:border-0 odd:bg-white even:bg-slate-50/60 hover:bg-indigo-50/60 transition-colors">
                    <td class="py-2.5 px-3 font-mono"><a class="text-indigo-600 hover:underline font-medium" href="{{ route('stocks.show', $stock) }}">{{ $stock->code }}</a></td>
                    <td class="py-2.5 px-3">
                        <a class="hover:underline hover:text-indigo-700" href="{{ route('stocks.show', $stock) }}">{{ $stock->stockName }}</a>
                    </td>
                    <td class="py-2.5 px-3 text-slate-500">{{ $stock->market?->market }}</td>
                    <td class="py-2.5 px-3"><x-score-badge :score="$stock->score" /></td>
                    <td class="py-2.5 px-3">
                        @foreach ($stock->themes as $theme)
                            <span class="inline-block bg-slate-100 text-slate-600 rounded-full px-2 py-0.5 text-xs mr-1 whitespace-nowrap">{{ $theme->name }}</span>
                        @endforeach
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-8 px-3 text-center text-slate-400">該当する銘柄がありません。</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $stocks->links() }}
</div>
@endsection
