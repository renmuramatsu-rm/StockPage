@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')

@foreach ($stocks as $stock)
<div>
    <h2>銘柄名: {{ $stock['stockName'] }}</h2>
    <p>証券コード: {{ $stock['code'] }}</p>
    <p>市場ID: {{ $stock['market_id'] }}</p>
</div>
@endforeach

@endsection