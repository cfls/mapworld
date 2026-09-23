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
<body
    class="bg-slate-50 min-h-screen text-slate-900"
    x-data="{
        mapMode: new URLSearchParams(window.location.search).get('mode') || 'pays',
        setMode(mode) {
            this.mapMode = mode;
            const url = new URL(window.location);
            url.searchParams.set('mode', mode);
            if (mode === 'pays') { url.searchParams.delete('zone'); }
            history.pushState({}, '', url);
            this.$nextTick(() => window.dispatchEvent(new CustomEvent('map-mode-changed', { detail: { mode } })));
        }
    }"
    x-on:switch-to-pays.window="
        setMode('pays');
        $nextTick(() => Livewire.dispatch('country-selected', { countryId: $event.detail.countryId }));
    "
>

    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:bg-blue-700 focus:text-white focus:px-4 focus:py-2 focus:rounded-lg focus:text-sm focus:font-semibold">
        Aller au contenu principal
    </a>

    {{-- Header: logo + titre + tabs + filtres --}}
    <header role="banner" class="fixed top-0 left-0 right-0 z-[1000] bg-white border-b border-slate-200">

        {{-- Ligne 1 : logo + marque + tabs + boutons commande --}}
        <div class="px-4 sm:px-6 lg:px-8 h-14 flex items-center gap-3">
            <img src="/images/cfls-logo.png" alt="CFLS" class="h-10 w-auto shrink-0">

            {{-- Tabs Pays / Mers --}}
            <nav aria-label="Section principale" class="flex items-center gap-1 shrink-0">
                <button
                    @click="setMode('pays')"
                    :aria-current="mapMode === 'pays' ? 'page' : false"
                    class="px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-1"
                    :class="mapMode === 'pays'
                        ? 'bg-blue-600 text-white shadow-sm'
                        : 'text-slate-600 hover:bg-slate-100'"
                >
                    <span class="hidden sm:inline">🗺️ Pays</span>
                    <span class="sm:hidden">🗺️</span>
                </button>
                <button
                    @click="setMode('mers')"
                    :aria-current="mapMode === 'mers' ? 'page' : false"
                    class="px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-1"
                    :class="mapMode === 'mers'
                        ? 'bg-sky-600 text-white shadow-sm'
                        : 'text-slate-600 hover:bg-slate-100'"
                >
                    <span class="hidden sm:inline">🌊 Mers</span>
                    <span class="sm:hidden">🌊</span>
                </button>
            </nav>

            <div class="flex items-center gap-2 min-w-0 flex-1">
                <span class="hidden sm:block text-slate-300 mx-1 select-none" aria-hidden="true">|</span>
                <p class="hidden sm:block text-xs sm:text-sm text-slate-500 truncate">
                    Signes des pays et mers du monde
                </p>
            </div>

            <div x-show="mapMode === 'pays'" class="flex items-center gap-2 shrink-0">
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

        {{-- Ligne 2 : filtres selon le mode actif --}}
        <div class="px-4 sm:px-6 lg:px-8 py-2 border-t border-slate-100">
            <div x-show="mapMode === 'pays'" x-cloak class="space-y-2">
                <livewire:continent-filter />
                <livewire:country-list />
            </div>
            <div x-show="mapMode === 'mers'" x-cloak class="space-y-2">
                <livewire:ocean-filter />
                <livewire:marine-list />
            </div>
        </div>
    </header>

    {{-- Contenu principal --}}
    <main
        id="main-content"
        x-data="{ headerHeight: 0 }"
        x-init="
            const header = document.querySelector('header');
            const update = () => { headerHeight = header.offsetHeight; };
            update();
            new ResizeObserver(update).observe(header);
        "
        :style="'padding-top: ' + (headerHeight + 12) + 'px'"
        class="px-4 sm:px-6 lg:px-8 pb-8 space-y-3"
    >
        {{-- Pays mode --}}
        <div id="panel-pays" x-show="mapMode === 'pays'" x-cloak class="space-y-3">

            {{-- Mobile: mapa → videos → info  /  Desktop: col1=(mapa+info) col2=videos --}}
            <div class="flex flex-col lg:grid lg:grid-cols-[3fr_2fr] lg:grid-rows-[auto_auto] gap-3 lg:items-start">

                {{-- 1. Carte interactive --}}
                <div class="order-1 lg:col-start-1 lg:row-start-1">
                    <livewire:world-map />
                </div>

                {{-- 2. Vidéos LSFB + INT --}}
                <div class="order-2 lg:col-start-2 lg:row-start-1 lg:row-span-2 space-y-3 lg:space-y-0 lg:sticky lg:h-[calc(100vh-8rem)] lg:flex lg:flex-col lg:gap-3"
                     :style="'top: ' + (headerHeight + 12) + 'px'"
                >
                    <livewire:country-detail />
                </div>

                {{-- 3. Info pays --}}
                <div class="order-3 lg:col-start-1 lg:row-start-2">
                    <livewire:country-info />
                </div>
            </div>

        </div>

        {{-- Mers et océans mode --}}
        <div id="panel-mers" x-show="mapMode === 'mers'" x-cloak class="space-y-3">

            {{-- Mobile: mapa → videos → info  /  Desktop: col1=(carte+info) col2=videos --}}
            <div class="flex flex-col lg:grid lg:grid-cols-[3fr_2fr] lg:grid-rows-[auto_auto] gap-3 lg:items-start">

                {{-- 1. Carte --}}
                <div class="order-1 lg:col-start-1 lg:row-start-1">
                    <livewire:ocean-map />
                </div>

                {{-- 2. Vidéos --}}
                <div class="order-2 lg:col-start-2 lg:row-start-1 lg:row-span-2 space-y-3 lg:space-y-0 lg:sticky lg:h-[calc(100vh-8rem)] lg:flex lg:flex-col lg:gap-3"
                     :style="'top: ' + (headerHeight + 12) + 'px'"
                >
                    <livewire:ocean-detail />
                </div>

                {{-- 3. Info mer --}}
                <div class="order-3 lg:col-start-1 lg:row-start-2">
                    <livewire:marine-info />
                </div>
            </div>

        </div>

    </main>

</body>
</html>
