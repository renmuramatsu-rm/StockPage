@extends('layouts.app')

@section('title', 'テーマを編集')

@section('content')
<div class="mb-3">
    <a href="{{ route('themes.dashboard') }}" class="text-sm text-slate-500 hover:text-indigo-600">&larr; ダッシュボードへ</a>
</div>
<h1 class="text-2xl font-bold mb-6 text-slate-900">テーマを編集</h1>

<form method="POST" action="{{ route('themes.update', $theme) }}" class="max-w-lg bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
    @method('PUT')
    @include('themes._form')
</form>

<form method="POST" action="{{ route('themes.destroy', $theme) }}" class="max-w-lg mt-4" onsubmit="return confirm('このテーマを削除しますか？');">
    @csrf
    @method('DELETE')
    <button type="submit" class="text-sm text-red-600 hover:underline">このテーマを削除</button>
</form>
@endsection
