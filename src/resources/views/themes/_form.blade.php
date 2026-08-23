@csrf

<div class="mb-4">
    <label class="block text-sm font-medium mb-1 text-slate-700" for="name">テーマ名</label>
    <input class="border border-slate-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" type="text" name="name" id="name" value="{{ old('name', $theme->name ?? '') }}" required>
    @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
</div>

<div class="mb-4">
    <label class="block text-sm font-medium mb-1 text-slate-700" for="description">説明</label>
    <textarea class="border border-slate-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" name="description" id="description" rows="3">{{ old('description', $theme->description ?? '') }}</textarea>
</div>

<div class="mb-6">
    <label class="block text-sm font-medium mb-1 text-slate-700" for="color">カラー（任意、例: #2563eb）</label>
    <input class="border border-slate-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" type="text" name="color" id="color" value="{{ old('color', $theme->color ?? '') }}">
</div>

<button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-indigo-700 transition">保存</button>
