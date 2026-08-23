<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '株式分析')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('css')
</head>

<body class="bg-slate-100 text-slate-900 min-h-screen">
    @php
        $navLinks = [
            ['route' => 'themes.dashboard', 'label' => 'テーマ別', 'active' => 'themes.*'],
            ['route' => 'stocks.index', 'label' => '銘柄一覧', 'active' => 'stocks.*'],
        ];
        if (auth()->check()) {
            $navLinks[] = ['route' => 'sbi-holdings.index', 'label' => 'SBI保有', 'active' => 'sbi-holdings.*'];
        }
    @endphp
    <header class="bg-slate-900 sticky top-0 z-20 shadow-lg shadow-slate-900/10">
        <div class="max-w-6xl mx-auto px-6 py-3.5 flex items-center justify-between flex-wrap gap-3">
            <a class="flex items-center gap-2 text-lg font-bold text-white tracking-tight" href="{{ route('themes.dashboard') }}">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-amber-500 text-slate-900 font-extrabold text-sm">株</span>
                <span>株式分析<span class="text-amber-400">.</span></span>
            </a>
            <div class="flex items-center gap-4">
                <nav>
                    <ul class="flex gap-1">
                        @foreach ($navLinks as $link)
                            <li>
                                <a href="{{ route($link['route']) }}"
                                   class="px-3.5 py-1.5 rounded-lg text-sm font-medium transition {{ request()->routeIs($link['active']) ? 'bg-white/10 text-amber-300' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                                    {{ $link['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
                <div class="w-px h-5 bg-white/10"></div>
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-slate-300 hover:text-white transition">ログアウト</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-slate-300 hover:text-white transition">ログイン</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto p-6">
        @yield('content')
    </main>

    <footer class="max-w-6xl mx-auto px-6 py-8 text-center text-xs text-slate-400">
        データ提供: J-Quants（JPX）・投資判断は機械的な参考指標であり、投資助言ではありません。
    </footer>
</body>

</html>
