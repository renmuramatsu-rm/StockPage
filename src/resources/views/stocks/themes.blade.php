@extends('layouts.app')

@section('title', $stock->stockName . ' | テーマを編集')

@section('content')
<div class="mb-3">
    <a href="{{ route('stocks.show', $stock) }}" class="text-sm text-slate-500 hover:text-indigo-600">&larr; {{ $stock->stockName }}へ</a>
</div>
<h1 class="text-2xl font-bold mb-6 text-slate-900">{{ $stock->stockName }} <span class="text-slate-400 font-mono text-lg">{{ $stock->code }}</span> のテーマ</h1>

<form method="POST" action="{{ route('stocks.themes.update', $stock) }}" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 max-w-lg">
    @csrf
    @method('PUT')

    <div class="mb-6 flex flex-col gap-1 max-h-96 overflow-y-auto">
        @forelse ($themes as $theme)
            <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 cursor-pointer">
                <input type="checkbox" name="theme_ids[]" value="{{ $theme->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    @checked($stock->themes->contains($theme->id))>
                <span class="text-slate-700">{{ $theme->name }}</span>
            </label>
        @empty
            <p class="text-slate-500">テーマがまだありません。<a class="text-indigo-600 hover:underline" href="{{ route('themes.create') }}">先にテーマを作成</a>してください。</p>
        @endforelse
    </div>

    <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-indigo-700 transition">保存</button>
</form>
@endsection
