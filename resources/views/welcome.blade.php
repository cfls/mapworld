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

    <meta property="og:type" content="website">
    <meta property="og:title" content="Les Pays du Monde — Signes en LSFB et Signes Internationaux">
    <meta property="og:description" content="Explorez les pays du monde avec des vidéos en LSFB et en Signes Internationaux pour chaque pays.">
    <meta property="og:image" content="{{ asset('images/cfls-logo.png') }}">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:locale" content="fr_BE">
    <meta property="og:site_name" content="Les Pays du Monde">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Les Pays du Monde — Signes en LSFB et Signes Internationaux">
    <meta name="twitter:description" content="Explorez les pays du monde avec des vidéos en LSFB et en Signes Internationaux pour chaque pays.">
    <meta name="twitter:image" content="{{ asset('images/cfls-logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 min-h-screen text-slate-900" x-data="{ mapMode: 'pays' }">

    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:bg-blue-700 focus:text-white focus:px-4 focus:py-2 focus:rounded-lg focus:text-sm focus:font-semibold">
        Aller au contenu principal
    </a>

    {{-- Header: logo + titre + pills --}}
    <header role="banner" class="fixed top-0 left-0 right-0 z-[1000] bg-white border-b border-slate-200">

        {{-- Ligne 1 : logo + marque + boutons commande --}}
        <div class="px-4 sm:px-6 lg:px-8 h-14 flex items-center gap-3">
            <img src="/images/cfls-logo.png" alt="CFLS" class="h-10 w-auto shrink-0">

            <div class="flex items-center gap-2 min-w-0 flex-1">
                <h1 class="text-sm sm:text-base font-bold text-blue-700 tracking-tight whitespace-nowrap">
                    LES PAYS DU MONDE
                </h1>
                <span class="hidden sm:block text-slate-300 mx-1 select-none" aria-hidden="true">|</span>
                <p class="hidden sm:block text-xs sm:text-sm text-slate-500 truncate">
                    Signes des pays du monde
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="https://cfls.be/boutique/l-europe"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="flex items-center gap-1.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap min-h-[36px]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332-.477-4.5-1.253" />
                    </svg>
                    <span class="hidden sm:inline">L'Europe</span>
                </a>
                <a href="https://cfls.be/boutique/les-pays-du-monde"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap min-h-[36px]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332-.477-4.5-1.253" />
                    </svg>
                    <span class="hidden sm:inline">Les pays du monde</span>
                </a>
            </div>
        </div>

        {{-- Ligne 2 : filtres continent (pills) --}}
        <div class="px-4 sm:px-6 lg:px-8 py-2 border-t border-slate-100">
            <livewire:continent-filter />
        </div>
    </header>

    {{-- Contenu principal --}}
    <main
        id="main-content"
        class="px-4 sm:px-6 lg:px-8 pb-8 space-y-3 pt-[7.5rem]"
    >
        {{-- Pays mode --}}
        <div id="panel-pays" x-show="mapMode === 'pays'" x-cloak class="space-y-3">

            {{-- Barre de pays / buscador --}}
            <livewire:country-list />

            {{-- Grille : mapa (60%) + détail (40%) --}}
            <div class="grid grid-cols-1 lg:grid-cols-[3fr_2fr] gap-3 items-start">

                {{-- Carte interactive + INFO --}}
                <div class="space-y-3">
                    <livewire:world-map />
                    <livewire:country-info />
                </div>

                {{-- Vidéos LSFB + INT --}}
                <div class="space-y-3 lg:space-y-0 lg:sticky lg:top-[7.5rem] lg:h-[calc(100vh-8rem)] lg:flex lg:flex-col lg:gap-3">
                    <livewire:country-detail />
                </div>
            </div>

        </div>

        {{-- Mers et océans mode --}}
        <div id="panel-mers" x-show="mapMode === 'mers'" x-cloak>
            <div class="flex flex-col lg:flex-row gap-4 items-start">
                <div class="w-full lg:w-2/3">
                    <livewire:ocean-map />
                </div>
                <div class="w-full lg:w-1/3 lg:sticky lg:top-20">
                    <livewire:ocean-detail />
                </div>
            </div>
        </div>

    </main>

</body>
</html>
