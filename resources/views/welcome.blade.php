<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Les Pays du Monde — Signes en LSFB et Signes Internationaux</title>
    <meta name="description" content="Explorez les pays du monde avec des vidéos en LSFB et en Signes Internationaux pour chaque pays.">
    <link rel="canonical" href="{{ url('/') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/cfls-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/cfls-logo.png') }}">

    <!-- Open Graph (Facebook, LinkedIn, etc.) -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Les Pays du Monde — Signes en LSFB et Signes Internationaux">
    <meta property="og:description" content="Explorez les pays du monde avec des vidéos en LSFB et en Signes Internationaux pour chaque pays.">
    <meta property="og:image" content="{{ asset('images/cfls-logo.png') }}">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:locale" content="fr_BE">
    <meta property="og:site_name" content="Les Pays du Monde">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Les Pays du Monde — Signes en LSFB et Signes Internationaux">
    <meta name="twitter:description" content="Explorez les pays du monde avec des vidéos en LSFB et en Signes Internationaux pour chaque pays.">
    <meta name="twitter:image" content="{{ asset('images/cfls-logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 min-h-screen text-slate-900">

    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:bg-indigo-700 focus:text-white focus:px-4 focus:py-2 focus:rounded-lg focus:text-sm focus:font-semibold">
        Aller au contenu principal
    </a>

    <header role="banner" class="bg-white border-b border-slate-200 fixed top-0 left-0 right-0 z-[1000]">
        <div class="w-full px-4 sm:px-6 lg:px-8 h-14 flex items-center gap-3">
            <img src="/images/cfls-logo.png" alt="CFLS" class="h-10 w-auto">
            <h1 class="hidden sm:block text-base font-bold text-indigo-700 tracking-tight">Les Pays du Monde</h1>
            <span class="hidden sm:block text-slate-300 text-sm" aria-hidden="true">|</span>
            <p class="text-xs text-slate-500 hidden sm:block">Signes des pays du monde en LSFB et Signes Internationaux</p>

            <div class="ml-auto flex items-center gap-2">
                <a href="https://cfls.be/boutique/l-europe"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332-.477-4.5-1.253" />
                    </svg>
                    <span class="hidden sm:inline">Commander L'Europe</span>
                    <span class="sm:hidden">Europe</span>
                </a>
                <a href="https://cfls.be/boutique/les-pays-du-monde"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332-.477-4.5-1.253" />
                    </svg>
                    <span class="hidden sm:inline">Commander Les pays du monde</span>
                    <span class="sm:hidden">Les pays du monde</span>
                </a>
            </div>
        </div>
    </header>

    {{-- Continent filter bar: fixed just below the header --}}
    <div class="fixed top-14 left-0 right-0 z-[999] bg-white border-b border-slate-200">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-2">
            <livewire:continent-filter />
        </div>
    </div>

    <main id="main-content" class="w-full px-4 sm:px-6 lg:px-8 pt-[6.5rem] pb-5 space-y-4">

        {{-- Mapa (izquierda) + Ficha de país (derecha) --}}

        <div id="map-section" class="flex flex-col lg:flex-row gap-4 items-start scroll-mt-28">
            <div class="w-full lg:w-2/3">
                <livewire:world-map />
            </div>
            <div class="w-full lg:w-1/3 lg:sticky lg:top-28">
                <livewire:country-detail />
            </div>
        </div>

        {{-- Lista de países --}}
        <livewire:country-list />

    </main>

</body>
</html>
