@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')

<div>
    <h1>日経平均株価: {{ $nikkei }}</h1>

</div>

@endsection