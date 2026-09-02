<?php

use App\Models\MarineArea;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function marineAreasByGeoJsonId(): array
    {
        return MarineArea::select('id', 'name', 'geojson_id', 'type')
            ->whereNotNull('geojson_id')
            ->get()
            ->keyBy('geojson_id')
            ->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'type' => $a->type])
            ->all();
    }
};
?>

<div>
    <script type="application/json" id="ocean-map-areas">{!! json_encode($this->marineAreasByGeoJsonId) !!}</script>

    <p class="sr-only">
        Carte interactive des mers et océans. Cliquez sur une zone pour afficher ses informations.
    </p>

    <div class="relative mt-10">
        <div
            wire:ignore
            id="ocean-map"
            role="application"
            aria-label="Carte interactive des mers et océans"
            class="w-full rounded-xl shadow-md
                   h-[300px]
                   sm:h-[400px]
                   md:h-[500px]
                   lg:h-[650px]
                   xl:h-[750px]"
        ></div>

        {{-- Zoom controls --}}
        <div wire:ignore class="absolute top-[52px] left-3 z-[500] flex flex-col rounded-lg shadow-md border border-slate-200 overflow-hidden">
            <button id="ocean-map-zoom-in" type="button" aria-label="Zoom avant"
                class="w-8 h-8 flex items-center justify-center bg-white hover:bg-slate-50 text-slate-700 text-lg font-semibold leading-none border-b border-slate-200 transition-colors">+</button>
            <button id="ocean-map-zoom-out" type="button" aria-label="Zoom arrière"
                class="w-8 h-8 flex items-center justify-center bg-white hover:bg-slate-50 text-slate-700 text-lg font-semibold leading-none transition-colors">−</button>
        </div>

        {{-- Map style selector --}}
        <div wire:ignore class="absolute top-[52px] right-3 z-[500] flex flex-col rounded-lg shadow-md border border-slate-200 overflow-hidden">
            <button id="ocean-style-satellite" type="button" title="Satellite"
                class="w-8 h-8 flex items-center justify-center border-b border-slate-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>
            <button id="ocean-style-light" type="button" title="Clair"
                class="w-8 h-8 flex items-center justify-center border-b border-slate-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                </svg>
            </button>
            <button id="ocean-style-dark" type="button" title="Sombre"
                class="w-8 h-8 flex items-center justify-center border-b border-slate-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>
            <button id="ocean-style-standard" type="button" title="Standard"
                class="w-8 h-8 flex items-center justify-center transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6-10l6-3m0 0l4.553 2.276A1 1 0 0121 7.618v10.764a1 1 0 01-1.447.894L15 17m0-13v13" />
                </svg>
            </button>
        </div>
    </div>
</div>

<style>
    #ocean-map .leaflet-interactive:focus { outline: none; }
</style>

<script>
    const _oceanMapEl = document.getElementById('ocean-map');
    if (_oceanMapEl && !_oceanMapEl._leaflet_id) {
        const marineAreasByGeoJsonId = JSON.parse(
            document.getElementById('ocean-map-areas').textContent
        );

        let geojsonLayer = null;
        let selectedLayer = null;

        const defaultStyle  = { fillColor: '#0284c7', weight: 1,   color: '#ffffff', fillOpacity: 0.25, opacity: 0.5 };
        const hoverStyle    = { fillColor: '#0369a1', weight: 1.5, color: '#ffffff', fillOpacity: 0.45, opacity: 0.8 };
        const selectedStyle = { fillColor: '#0c4a6e', weight: 1.5, color: '#ffffff', fillOpacity: 0.65, opacity: 0.9 };
        const unknownStyle  = { fillColor: '#94a3b8', weight: 0.5, color: '#ffffff', fillOpacity: 0.05, opacity: 0.2 };

        function styleForFeature(feature) {
            const neId = String(feature.properties?.ne_id ?? '');
            return marineAreasByGeoJsonId[neId] ? defaultStyle : unknownStyle;
        }

        function onEachFeature(feature, layer) {
            const neId = String(feature.properties?.ne_id ?? '');
            const area = marineAreasByGeoJsonId[neId];
            const label = feature.properties?.name_fr ?? feature.properties?.name ?? '';

            layer.on({
                mouseover(e) {
                    if (!area) return;
                    if (e.target === selectedLayer) return;
                    e.target.setStyle(hoverStyle);
                },
                mouseout(e) {
                    if (e.target === selectedLayer) return;
                    geojsonLayer.resetStyle(e.target);
                    if (!area) e.target.setStyle(unknownStyle);
                },
                click(e) {
                    if (!area) return;
                    if (selectedLayer) geojsonLayer.resetStyle(selectedLayer);
                    selectedLayer = e.target;
                    e.target.setStyle(selectedStyle);
                    e.target.getElement()?.blur();
                    try {
                        map.flyToBounds(e.target.getBounds(), { maxZoom: 5, padding: [40, 40], duration: 0.8 });
                    } catch (_) {}
                    $wire.$dispatch('marine-area-selected', { marineAreaId: area.id });
                },
            });

            if (label) layer.bindTooltip(label, { sticky: true });
        }

        const map = L.map('ocean-map', {
            center: [20, 0],
            zoom: 2,
            minZoom: 1,
            maxZoom: 8,
            worldCopyJump: true,
            zoomControl: false,
        });

        const OCEAN_MAP_STYLES = {
            satellite: {
                url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                attribution: 'Tiles &copy; Esri &mdash; Esri, i-cubed, USDA, USGS, AEX, GeoEye',
            },
            light: {
                url: 'https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}',
                attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ',
            },
            dark: {
                url: 'https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}',
                attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ',
            },
            standard: {
                url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            },
        };

        const OCEAN_STYLE_KEY = 'mapworld-ocean-tile-style';
        let currentTileLayer = null;

        function applyOceanStyle(styleKey) {
            const def = OCEAN_MAP_STYLES[styleKey] || OCEAN_MAP_STYLES.satellite;
            if (currentTileLayer) { map.removeLayer(currentTileLayer); currentTileLayer = null; }
            currentTileLayer = L.tileLayer(def.url, { attribution: def.attribution }).addTo(map);
            localStorage.setItem(OCEAN_STYLE_KEY, styleKey);

            Object.keys(OCEAN_MAP_STYLES).forEach(key => {
                const btn = document.getElementById(`ocean-style-${key}`);
                if (!btn) return;
                if (key === styleKey) {
                    btn.classList.remove('bg-white', 'text-slate-700', 'hover:bg-slate-50');
                    btn.classList.add('bg-sky-600', 'text-white');
                } else {
                    btn.classList.remove('bg-sky-600', 'text-white');
                    btn.classList.add('bg-white', 'text-slate-700', 'hover:bg-slate-50');
                }
            });
        }

        applyOceanStyle(localStorage.getItem(OCEAN_STYLE_KEY) || 'satellite');
        Object.keys(OCEAN_MAP_STYLES).forEach(key => {
            document.getElementById(`ocean-style-${key}`)?.addEventListener('click', () => applyOceanStyle(key));
        });

        document.getElementById('ocean-map-zoom-in')?.addEventListener('click', () => map.zoomIn());
        document.getElementById('ocean-map-zoom-out')?.addEventListener('click', () => map.zoomOut());

        fetch('/geojson/world-oceans.json?v={{ filemtime(public_path("geojson/world-oceans.json")) }}')
            .then(r => r.json())
            .then(data => {
                geojsonLayer = L.geoJSON(data, {
                    style: styleForFeature,
                    onEachFeature,
                }).addTo(map);
            });

        window.addEventListener('resize', () => map?.invalidateSize());
    }
</script>
