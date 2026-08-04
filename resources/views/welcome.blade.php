<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CountryWorld — Signes des pays du monde</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 min-h-screen text-slate-900">

    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:bg-indigo-700 focus:text-white focus:px-4 focus:py-2 focus:rounded-lg focus:text-sm focus:font-semibold">
        Aller au contenu principal
    </a>

    <header role="banner" class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center gap-3">
            <img src="/images/cfls-logo.png" alt="CFLS" class="h-10 w-auto">
            <h1 class="text-base font-bold text-indigo-700 tracking-tight">Les Pays du Monde</h1>
            <span class="text-slate-300 text-sm" aria-hidden="true">|</span>
            <p class="text-xs text-slate-500 hidden sm:block">Signes des pays du monde en LSFB et Signes Internationaux</p>
        </div>
    </header>

    <main id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 space-y-4">

        {{-- Filtro de continentes --}}
        <livewire:continent-filter />

        {{-- Mapa (izquierda) + Ficha de país (derecha) --}}
        <div class="flex flex-col lg:flex-row gap-4 items-start">
            <div class="w-full lg:w-2/3">
                <livewire:world-map />
            </div>
            <div class="w-full lg:w-1/3 lg:sticky lg:top-20">
                <livewire:country-detail />
            </div>
        </div>

        {{-- Lista de países --}}
        <livewire:country-list />

    </main>

</body>
</html>
