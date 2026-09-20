<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin — CountryWorld</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 min-h-screen text-slate-900">

    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 shrink-0">
                <span class="text-xl">🌍</span>
                <a href="{{ route('admin.countries') }}" class="text-base font-bold text-indigo-700 tracking-tight hover:text-indigo-800 hidden sm:block">
                    CountryWorld
                </a>
                <span class="text-slate-300 text-sm hidden sm:block">|</span>
                <span class="text-xs text-slate-500 font-medium uppercase tracking-wide hidden sm:block">Administration</span>
            </div>

            {{-- Section tabs --}}
            @auth
                <nav class="flex items-center gap-1 bg-slate-100 rounded-lg p-1">
                    <a href="{{ route('admin.countries') }}"
                       class="flex items-center gap-1.5 px-4 py-1.5 rounded-md text-sm font-semibold transition-colors
                              {{ request()->routeIs('admin.countries', 'admin.videos') ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                        🌍 <span>Pays</span>
                    </a>
                    <a href="{{ route('admin.marine-areas') }}"
                       class="flex items-center gap-1.5 px-4 py-1.5 rounded-md text-sm font-semibold transition-colors
                              {{ request()->routeIs('admin.marine-areas', 'admin.marine-area-videos') ? 'bg-white text-sky-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                        🌊 <span>Mers</span>
                    </a>
                </nav>
            @endauth

            <div class="flex items-center gap-4 shrink-0">
                <a href="{{ route('home') }}" class="text-slate-400 hover:text-slate-600 text-xs hidden sm:block">
                    ← Site public
                </a>
                @auth
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-xs text-slate-500 hover:text-red-600 font-medium transition-colors">
                            Déconnexion
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

</body>
</html>
