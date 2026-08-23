@props(['stocks', 'name' => 'code', 'selected' => null, 'placeholder' => '銘柄コードまたは銘柄名で検索'])

@php
    $selectedStock = $selected ? $stocks->firstWhere('code', $selected) : null;
@endphp

<div class="relative" data-stock-search>
    <input type="hidden" name="{{ $name }}" value="{{ $selected }}" data-stock-search-value>
    <input
        type="text"
        autocomplete="off"
        placeholder="{{ $placeholder }}"
        value="{{ $selectedStock ? $selectedStock->code.' - '.$selectedStock->stockName : '' }}"
        {{ $attributes->merge(['class' => 'border rounded-lg w-full px-3 py-2']) }}
        data-stock-search-input
    >
    <div class="absolute z-20 mt-1 w-full bg-white border rounded-lg shadow-lg max-h-64 overflow-y-auto hidden" data-stock-search-list></div>
    <script type="application/json" data-stock-search-data>{!! $stocks->map(fn ($s) => ['code' => $s->code, 'name' => $s->stockName])->values()->toJson() !!}</script>
</div>
