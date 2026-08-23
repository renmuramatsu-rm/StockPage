@extends('layouts.app')

@section('title', '保有銘柄を追加')

@section('content')
<div class="mb-3">
    <a href="{{ route('sbi-holdings.index') }}" class="text-sm text-slate-500 hover:text-indigo-600">&larr; SBI保有銘柄へ</a>
</div>
<h1 class="text-2xl font-bold mb-6 text-slate-900">保有銘柄を追加</h1>

<form method="POST" action="{{ route('sbi-holdings.store') }}" class="max-w-lg bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
    @include('sbi_holdings._form')
</form>
@endsection
