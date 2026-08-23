@extends('layouts.app')

@section('title', '新しいテーマ')

@section('content')
<div class="mb-3">
    <a href="{{ route('themes.dashboard') }}" class="text-sm text-slate-500 hover:text-indigo-600">&larr; ダッシュボードへ</a>
</div>
<h1 class="text-2xl font-bold mb-6 text-slate-900">新しいテーマ</h1>

<form method="POST" action="{{ route('themes.store') }}" class="max-w-lg bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
    @include('themes._form')
</form>
@endsection
