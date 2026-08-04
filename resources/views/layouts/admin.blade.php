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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="text-xl">🌍</span>
                <a href="{{ route('admin.countries') }}" class="text-base font-bold text-indigo-700 tracking-tight hover:text-indigo-800">
                    CountryWorld
                </a>
                <span class="text-slate-300 text-sm">|</span>
                <span class="text-xs text-slate-500 font-medium uppercase tracking-wide">Administration</span>
            </div>
            <div class="flex items-center gap-5">
                <nav class="hidden sm:flex items-center gap-5 text-sm">
                    <a href="{{ route('admin.countries') }}"
                       class="font-medium {{ request()->routeIs('admin.countries') ? 'text-indigo-700' : 'text-slate-600 hover:text-indigo-700' }}">
                        Pays
                    </a>
                    <a href="{{ route('home') }}" class="text-slate-400 hover:text-slate-600 text-xs">
                        ← Site public
                    </a>
                </nav>
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
