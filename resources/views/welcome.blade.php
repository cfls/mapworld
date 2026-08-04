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

    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center gap-3">
            <span class="text-xl">🌍</span>
            <h1 class="text-base font-bold text-indigo-700 tracking-tight">CountryWorld</h1>
            <span class="text-slate-300 text-sm">|</span>
            <p class="text-xs text-slate-500">Signes des pays du monde en LSFB et Signes Internationaux</p>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 space-y-4">

        <livewire:continent-filter />

        <livewire:world-map />

        <livewire:country-list />

    </main>

</body>
</html>
