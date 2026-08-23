@extends('layouts.app')

@section('title', 'テーマ別ダッシュボード')

@section('content')
<div class="rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white p-6 sm:p-8 mb-8 shadow-xl shadow-slate-900/20">
    <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">テーマ別ダッシュボード</h1>
            <p class="text-slate-400 text-sm mt-1">業種テーマは自動更新、独自テーマは手動で追加できます</p>
        </div>
        <div class="bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-sm">
            <span class="text-slate-400">日経平均</span>
            <span class="ml-2 font-mono font-semibold text-amber-300">{{ $nikkei }}</span>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-6">
        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
            <div class="text-slate-400 text-xs">追跡銘柄数</div>
            <div class="text-2xl font-bold font-mono mt-1">{{ number_format($stats['stocks']) }}</div>
        </div>
        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
            <div class="text-slate-400 text-xs">評価済み銘柄</div>
            <div class="text-2xl font-bold font-mono mt-1">{{ number_format($stats['scored']) }}</div>
        </div>
        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
            <div class="text-slate-400 text-xs">買い候補</div>
            <div class="text-2xl font-bold font-mono mt-1 text-emerald-400">{{ number_format($stats['buy_candidates']) }}</div>
        </div>
        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
            <div class="text-slate-400 text-xs">最終更新</div>
            <div class="text-sm font-medium mt-2">{{ $stats['last_synced']?->diffForHumans() ?? '未実行' }}</div>
        </div>
    </div>
</div>

<div class="mb-4 flex justify-between items-center flex-wrap gap-2">
    <p class="text-sm text-slate-500">{{ $themes->count() }} テーマ</p>
    @auth
        <a href="{{ route('themes.create') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">+ 新しいテーマを作成</a>
    @endauth
</div>

@if ($themes->isEmpty())
    <p class="text-slate-500">テーマがまだありません。<code class="bg-slate-200 px-1.5 py-0.5 rounded text-xs">php artisan stocks:sync</code> を実行すると業種テーマが自動で作成されます。</p>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach ($themes as $theme)
        <a href="{{ route('themes.show', $theme) }}" class="group block border border-slate-200 rounded-xl shadow-sm bg-white p-4 hover:shadow-lg hover:shadow-slate-200 hover:-translate-y-0.5 hover:border-indigo-200 transition-all duration-150">
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-block w-2.5 h-2.5 rounded-full" style="background-color: {{ $theme->color ?? '#94a3b8' }}"></span>
                <h2 class="font-semibold text-slate-800 group-hover:text-indigo-700">{{ $theme->name }}</h2>
                @if ($theme->source !== 'manual')
                    <span class="text-[10px] text-slate-400 bg-slate-100 rounded px-1.5 py-0.5 ml-auto">業種</span>
                @endif
            </div>
            @if ($theme->description)
                <p class="text-sm text-slate-500 mb-2">{{ $theme->description }}</p>
            @endif
            <div class="text-sm text-slate-500 font-mono">{{ $theme->stocks_count }} 銘柄</div>
        </a>
    @endforeach
</div>
@endsection
