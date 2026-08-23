@csrf

<div class="mb-4">
    <label class="block text-sm font-medium mb-1 text-slate-700" for="code">銘柄コード</label>
    @if (isset($holding))
        <div class="border border-slate-200 rounded-lg w-full px-3 py-2 bg-slate-50 text-slate-600">{{ $holding->code }} - {{ $holding->stock?->stockName }}</div>
        <input type="hidden" name="code" value="{{ $holding->code }}">
        <p class="text-xs text-slate-400 mt-1">銘柄の変更はできません。別の銘柄で登録し直してください。</p>
    @else
        <x-stock-search :stocks="$stocks" :selected="old('code')" />
    @endif
    @error('code') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
</div>

<div class="mb-4">
    <label class="block text-sm font-medium mb-1 text-slate-700" for="shares">株数</label>
    <input class="border border-slate-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" type="number" min="1" name="shares" id="shares" value="{{ old('shares', $holding->shares ?? '') }}" required>
    @error('shares') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
</div>

<div class="mb-4">
    <label class="block text-sm font-medium mb-1 text-slate-700" for="average_acquisition_price">取得単価（平均）</label>
    <input class="border border-slate-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" type="number" step="0.01" min="0" name="average_acquisition_price" id="average_acquisition_price" value="{{ old('average_acquisition_price', $holding->average_acquisition_price ?? '') }}" required>
    @error('average_acquisition_price') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
</div>

<div class="mb-4">
    <label class="block text-sm font-medium mb-1 text-slate-700" for="acquisition_date">取得日</label>
    <input class="border border-slate-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" type="date" name="acquisition_date" id="acquisition_date" value="{{ old('acquisition_date', optional($holding->acquisition_date ?? null)->format('Y-m-d')) }}">
</div>

<div class="mb-4">
    <label class="block text-sm font-medium mb-1 text-slate-700" for="account_type">口座区分</label>
    <select class="border border-slate-300 rounded-lg w-full px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" name="account_type" id="account_type">
        <option value="">未選択</option>
        <option value="specific" @selected(old('account_type', $holding->account_type ?? '') == 'specific')>特定口座</option>
        <option value="general" @selected(old('account_type', $holding->account_type ?? '') == 'general')>一般口座</option>
        <option value="nisa" @selected(old('account_type', $holding->account_type ?? '') == 'nisa')>NISA</option>
    </select>
</div>

<div class="mb-6">
    <label class="block text-sm font-medium mb-1 text-slate-700" for="memo">メモ</label>
    <textarea class="border border-slate-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" name="memo" id="memo" rows="2">{{ old('memo', $holding->memo ?? '') }}</textarea>
</div>

<button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-indigo-700 transition">保存</button>
