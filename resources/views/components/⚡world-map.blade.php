<?php

use App\Models\Country;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    #[On('map-reset')]
    public function mapReset(): void {}

    #[Computed]
    public function countriesByIso(): array
    {
        return Country::select('id', 'iso3', 'continent_id', 'name')
            ->whereNotNull('iso3')
            ->get()
            ->keyBy('iso3')
            ->map(fn ($c) => ['id' => $c->id, 'continentId' => $c->continent_id, 'name' => $c->name])
            ->all();
    }

    #[Computed]
    public function countriesById(): array
    {
        return Country::select('id', 'latitude', 'longitude')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->keyBy('id')
            ->map(fn ($c) => ['lat' => (float) $c->latitude, 'lng' => (float) $c->longitude])
            ->all();
    }
};
?>

<div>
    {{-- JSON data carriers: read once by the script on mount --}}
    <script type="application/json" id="world-map-countries">{!! json_encode($this->countriesByIso) !!}</script>
    <script type="application/json" id="world-map-coords">{!! json_encode($this->countriesById) !!}</script>

    <p class="sr-only">
        Carte mondiale interactive. Cliquez sur un pays pour afficher ses vidéos en langue des signes.
        Vous pouvez également utiliser la barre de recherche et la liste des pays ci-dessous.
    </p>

    <div class="relative mt-10">
        <div
            wire:ignore
            id="world-map"
            role="application"
            aria-label="Carte mondiale interactive — sélectionnez un pays"
            class="w-full rounded-xl shadow-md
                   h-[300px]
                   sm:h-[400px]
                   md:h-[500px]
                   lg:h-[650px]
                   xl:h-[750px]"
        ></div>

        {{-- Zoom controls --}}
        <div wire:ignore class="absolute top-[52px] left-3 z-[500] flex flex-col rounded-lg shadow-md border border-slate-200 overflow-hidden">
            <button
                id="map-zoom-in"
                type="button"
                aria-label="Zoom avant"
                class="w-8 h-8 flex items-center justify-center bg-white hover:bg-slate-50 text-slate-700 text-lg font-semibold leading-none border-b border-slate-200 transition-colors"
            >+</button>
            <button
                id="map-zoom-out"
                type="button"
                aria-label="Zoom arrière"
                class="w-8 h-8 flex items-center justify-center bg-white hover:bg-slate-50 text-slate-700 text-lg font-semibold leading-none transition-colors"
            >−</button>
        </div>

        {{-- Map style selector --}}
        <div wire:ignore class="absolute top-[52px] right-3 z-[500] flex flex-col rounded-lg shadow-md border border-slate-200 overflow-hidden">
            {{-- Satellite --}}
            <button id="map-style-satellite" type="button" title="Satellite"
                class="w-8 h-8 flex items-center justify-center border-b border-slate-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>
            {{-- Clair --}}
            <button id="map-style-light" type="button" title="Clair"
                class="w-8 h-8 flex items-center justify-center border-b border-slate-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                </svg>
            </button>
            {{-- Sombre --}}
            <button id="map-style-dark" type="button" title="Sombre"
                class="w-8 h-8 flex items-center justify-center border-b border-slate-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>
            {{-- Standard --}}
            <button id="map-style-standard" type="button" title="Standard"
                class="w-8 h-8 flex items-center justify-center border-b border-slate-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6-10l6-3m0 0l4.553 2.276A1 1 0 0121 7.618v10.764a1 1 0 01-1.447.894L15 17m0-13v13" />
                </svg>
            </button>
            {{-- Coloré --}}
            <button id="map-style-colorful" type="button" title="Coloré"
                class="w-8 h-8 flex items-center justify-center transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 16 16">
                    <rect x="1" y="1" width="6" height="6" rx="1" fill="#F06292"/>
                    <rect x="9" y="1" width="6" height="6" rx="1" fill="#42A5F5"/>
                    <rect x="1" y="9" width="6" height="6" rx="1" fill="#FFA726"/>
                    <rect x="9" y="9" width="6" height="6" rx="1" fill="#66BB6A"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<style>
    #world-map .leaflet-interactive:focus { outline: none; }
</style>

<script>
    // Guard: run initialization only once per map container lifecycle.
    // wire:ignore preserves the #world-map element across Livewire re-renders,
    // so _leaflet_id persists and prevents double-initialization.
    const _mapEl = document.getElementById('world-map');
    if (_mapEl && !_mapEl._leaflet_id) {
        const countriesByIso = JSON.parse(
            document.getElementById('world-map-countries').textContent
        );
        const countriesById = JSON.parse(
            document.getElementById('world-map-coords').textContent
        );

        let geojsonLayer = null;
        let selectedContinentId = null;
        let selectedLayer = null;
        let isColorfulMode = false;

        const defaultStyle     = { fillColor: '#4f46e5', weight: 1,   color: '#ffffff', fillOpacity: 0.35, opacity: 0.6  };
        const dimmedStyle      = { fillColor: '#94a3b8', weight: 0.5, color: '#ffffff', fillOpacity: 0.04, opacity: 0.2  };
        const hoverStyle       = { fillColor: '#3730a3', weight: 1.5, color: '#ffffff', fillOpacity: 0.55, opacity: 0.8  };
        const selectedStyle    = { fillColor: '#16a34a', weight: 1.5, color: '#ffffff', fillOpacity: 0.65, opacity: 0.9  };
        const transparentStyle = { fillColor: '#000000', weight: 0.7, color: '#ffffff', fillOpacity: 0,    opacity: 0.35 };

        const COLORFUL_PALETTE = [
            '#F06292', '#42A5F5', '#FFA726', '#66BB6A',
            '#BA68C8', '#26C6DA', '#EF5350', '#D4E157',
            '#FF7043', '#5C6BC0', '#EC407A', '#26A69A',
            '#FFCA28', '#8D6E63',
        ];

        function colorForFeature(featureId) {
            let hash = 0;
            for (let i = 0; i < featureId.length; i++) {
                hash = (hash << 5) - hash + featureId.charCodeAt(i);
                hash |= 0;
            }
            return COLORFUL_PALETTE[Math.abs(hash) % COLORFUL_PALETTE.length];
        }

        function updateBorderColor(color) {
            [defaultStyle, dimmedStyle, hoverStyle, selectedStyle, transparentStyle].forEach(s => { s.color = color; });
        }

        function styleForFeature(feature) {
            const country = countriesByIso[feature.id];
            if (isColorfulMode) {
                if (!country) {
                    return { fillColor: '#e2e8f0', weight: 0.5, color: '#ffffff', fillOpacity: 0.4, opacity: 0.4 };
                }
                if (selectedContinentId !== null && country.continentId !== selectedContinentId) {
                    return { fillColor: '#cbd5e1', weight: 0.5, color: '#ffffff', fillOpacity: 0.3, opacity: 0.4 };
                }
                return { fillColor: colorForFeature(feature.id), weight: 0.5, color: '#ffffff', fillOpacity: 1, opacity: 1 };
            }
            if (selectedContinentId === null) {
                return transparentStyle;
            }
            if (!country) return dimmedStyle;
            return country.continentId === selectedContinentId ? defaultStyle : dimmedStyle;
        }

        function onEachFeature(feature, layer) {
            const country = countriesByIso[feature.id];

            layer.on({
                mouseover(e) {
                    if (!country) return;
                    if (selectedContinentId !== null && country.continentId !== selectedContinentId) return;
                    if (e.target === selectedLayer) return;
                    if (isColorfulMode) {
                        e.target.setStyle({
                            fillColor: colorForFeature(feature.id),
                            weight: 2,
                            color: '#ffffff',
                            fillOpacity: 1,
                            opacity: 1,
                        });
                    } else {
                        e.target.setStyle(hoverStyle);
                    }
                },
                mouseout(e) {
                    if (e.target === selectedLayer) return;
                    geojsonLayer.resetStyle(e.target);
                    if (!isColorfulMode && selectedContinentId !== null) {
                        if (!country || country.continentId !== selectedContinentId) {
                            e.target.setStyle(dimmedStyle);
                        }
                    }
                },
                click(e) {
                    if (!country) return;
                    if (selectedLayer) {
                        geojsonLayer.resetStyle(selectedLayer);
                        const prevCountry = countriesByIso[selectedLayer.feature.id];
                        if (!isColorfulMode && selectedContinentId !== null && (!prevCountry || prevCountry.continentId !== selectedContinentId)) {
                            selectedLayer.setStyle(dimmedStyle);
                        }
                    }
                    const alreadySelected = selectedLayer === e.target;
                    selectedLayer = e.target;
                    e.target.setStyle(selectedStyle);
                    e.target.getElement()?.blur();
                    if (!alreadySelected) {
                        try {
                            map.flyToBounds(e.target.getBounds(), { maxZoom: 7, padding: [40, 40], duration: 0.8 });
                        } catch (_) {}
                    }
                    $wire.$dispatch('country-selected', { countryId: country.id, continentId: country.continentId });
                },
            });

            const tooltipName = country?.name ?? feature.properties?.name;
            if (tooltipName) {
                layer.bindTooltip(tooltipName, { sticky: true });
            }
        }

        const map = L.map('world-map', {
            center: [20, 0],
            zoom: 2,
            minZoom: 1,
            maxZoom: 8,
            worldCopyJump: true,
            zoomControl: false,
        });

        // --- Map style system ---
        const MAP_STYLES = {
            satellite: {
                url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
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
            colorful: {
                url: null,
                attribution: '',
            },
        };

        const STYLE_KEY = 'mapworld-tile-style';
        let currentTileLayer = null;

        function applyMapStyle(styleKey) {
            const def = MAP_STYLES[styleKey] || MAP_STYLES.satellite;
            if (currentTileLayer) {
                map.removeLayer(currentTileLayer);
                currentTileLayer = null;
            }
            if (def.url) {
                currentTileLayer = L.tileLayer(def.url, { attribution: def.attribution }).addTo(map);
            }

            isColorfulMode = styleKey === 'colorful';
            localStorage.setItem(STYLE_KEY, styleKey);

            map.getContainer().style.background = isColorfulMode ? '#ffffff' : '';

            if (!isColorfulMode) {
                const isLightTile = styleKey === 'light' || styleKey === 'standard';
                updateBorderColor(isLightTile ? '#475569' : '#ffffff');
            }

            if (geojsonLayer) {
                geojsonLayer.eachLayer(layer => {
                    if (!layer.feature) return;
                    layer.setStyle(styleForFeature(layer.feature));
                });
                if (selectedLayer) {
                    selectedLayer.setStyle(selectedStyle);
                }
            }

            Object.keys(MAP_STYLES).forEach(key => {
                const btn = document.getElementById(`map-style-${key}`);
                if (!btn) return;
                if (key === styleKey) {
                    btn.classList.remove('bg-white', 'text-slate-700', 'hover:bg-slate-50');
                    btn.classList.add('bg-indigo-600', 'text-white');
                } else {
                    btn.classList.remove('bg-indigo-600', 'text-white');
                    btn.classList.add('bg-white', 'text-slate-700', 'hover:bg-slate-50');
                }
            });
        }

        applyMapStyle(localStorage.getItem(STYLE_KEY) || 'satellite');

        Object.keys(MAP_STYLES).forEach(key => {
            document.getElementById(`map-style-${key}`)?.addEventListener('click', () => applyMapStyle(key));
        });
        // --- end map style system ---

        document.getElementById('map-zoom-in')?.addEventListener('click', () => map.zoomIn());
        document.getElementById('map-zoom-out')?.addEventListener('click', () => map.zoomOut());

        fetch('/geojson/world-countries.json?v={{ filemtime(public_path("geojson/world-countries.json")) }}')
            .then(r => r.json())
            .then(data => {
                geojsonLayer = L.geoJSON(data, {
                    style: styleForFeature,
                    onEachFeature,
                }).addTo(map);
            });

        // Country selected (from list or map click): highlight green and fly to it
        Livewire.on('country-selected', ({ countryId }) => {
            if (!geojsonLayer) return;
            let layerFound = false;
            geojsonLayer.eachLayer(layer => {
                if (!layer.feature) return;
                const country = countriesByIso[layer.feature.id];
                if (!country || country.id !== countryId) return;

                layerFound = true;

                if (selectedLayer && selectedLayer !== layer) {
                    geojsonLayer.resetStyle(selectedLayer);
                    const prevCountry = countriesByIso[selectedLayer.feature.id];
                    if (!isColorfulMode && selectedContinentId !== null && (!prevCountry || prevCountry.continentId !== selectedContinentId)) {
                        selectedLayer.setStyle(dimmedStyle);
                    }
                }

                const alreadySelected = selectedLayer === layer;
                selectedLayer = layer;
                layer.setStyle(selectedStyle);
                layer.getElement()?.blur();

                if (!alreadySelected) {
                    try {
                        map.flyToBounds(layer.getBounds(), { maxZoom: 7, padding: [40, 40], duration: 0.8 });
                    } catch (e) { /* layer may have no bounds (point feature) */ }
                }
            });

            // Territory without GeoJSON polygon (no iso3): fly to stored coordinates
            if (!layerFound) {
                if (selectedLayer) {
                    geojsonLayer.resetStyle(selectedLayer);
                    const prevCountry = countriesByIso[selectedLayer.feature.id];
                    if (!isColorfulMode && selectedContinentId !== null && (!prevCountry || prevCountry.continentId !== selectedContinentId)) {
                        selectedLayer.setStyle(dimmedStyle);
                    }
                    selectedLayer = null;
                }
                const coords = countriesById[countryId];
                if (coords) {
                    map.flyTo([coords.lat, coords.lng], 7, { duration: 0.8 });
                }
            }
        });

        // Continent filter: reset view and update map styles without a server roundtrip
        Livewire.on('continent-selected', ({ continentId }) => {
            selectedContinentId = continentId ?? null;
            selectedLayer = null;
            map.flyTo([20, 0], 2, { duration: 0.8 });
            if (!geojsonLayer) return;
            geojsonLayer.eachLayer(layer => {
                if (!layer.feature) return;
                layer.setStyle(styleForFeature(layer.feature));
            });
        });

        // Reset: fly back to world view and clear selection
        Livewire.on('map-reset', () => {
            map.flyTo([20, 0], 2, { duration: 0.8 });
            if (selectedLayer && geojsonLayer) {
                geojsonLayer.resetStyle(selectedLayer);
                const prevCountry = countriesByIso[selectedLayer.feature.id];
                if (!isColorfulMode && selectedContinentId !== null && (!prevCountry || prevCountry.continentId !== selectedContinentId)) {
                    selectedLayer.setStyle(dimmedStyle);
                }
                selectedLayer = null;
            }
        });

        window.addEventListener('resize', () => map?.invalidateSize());
    }
</script>
