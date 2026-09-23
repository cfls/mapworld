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
            ->whereHas('signVideos')
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
            ->whereHas('signVideos')
            ->get()
            ->keyBy('id')
            ->map(fn ($c) => ['lat' => (float) $c->latitude, 'lng' => (float) $c->longitude])
            ->all();
    }
};
?>

<div>
    <script type="application/json" id="world-map-countries">{!! json_encode($this->countriesByIso) !!}</script>
    <script type="application/json" id="world-map-coords">{!! json_encode($this->countriesById) !!}</script>

    <p class="sr-only">
        Carte mondiale interactive. Cliquez sur un pays pour afficher ses vidéos en langue des signes.
        Vous pouvez également utiliser la barre de recherche ci-dessus.
    </p>

    <div class="relative mt-10">
        <div
            wire:ignore
            id="world-map"
            role="application"
            aria-label="Carte mondiale interactive — sélectionnez un pays"
            class="w-full rounded-2xl shadow-sm border border-slate-200 z-0
                   h-[42vh]
                   sm:h-[45vh]
                   md:h-[420px]
                   lg:h-[520px]
                   xl:h-[600px]"
        ></div>

        {{-- Contrôles : zoom + reset + styles (colonne droite) --}}
        <div wire:ignore class="absolute top-3 right-3 z-[500] flex flex-col gap-2">

            {{-- Zoom + reset --}}
            <div class="flex flex-col rounded-xl shadow-md border border-slate-200 overflow-hidden bg-white">
                <button
                    id="map-zoom-in"
                    type="button"
                    aria-label="Zoom avant"
                    class="w-10 h-10 flex items-center justify-center text-slate-700 hover:bg-slate-50 text-lg font-semibold leading-none border-b border-slate-200 transition-colors"
                >+</button>
                <button
                    id="map-zoom-out"
                    type="button"
                    aria-label="Zoom arrière"
                    class="w-10 h-10 flex items-center justify-center text-slate-700 hover:bg-slate-50 text-lg font-semibold leading-none border-b border-slate-200 transition-colors"
                >−</button>
                <button
                    id="map-reset-btn"
                    type="button"
                    aria-label="Réinitialiser la vue"
                    class="w-10 h-10 flex items-center justify-center text-slate-700 hover:bg-slate-50 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </button>
            </div>

            {{-- Sélecteur de style de carte --}}
            <div class="flex flex-col rounded-xl shadow-md border border-slate-200 overflow-hidden bg-white">
                <button id="map-style-satellite" type="button" title="Satellite"
                    class="w-10 h-10 flex items-center justify-center border-b border-slate-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>
                <button id="map-style-light" type="button" title="Clair"
                    class="w-10 h-10 flex items-center justify-center border-b border-slate-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                    </svg>
                </button>
                <button id="map-style-dark" type="button" title="Sombre"
                    class="w-10 h-10 flex items-center justify-center border-b border-slate-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>
                <button id="map-style-standard" type="button" title="Standard"
                    class="w-10 h-10 flex items-center justify-center border-b border-slate-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6-10l6-3m0 0l4.553 2.276A1 1 0 0121 7.618v10.764a1 1 0 01-1.447.894L15 17m0-13v13" />
                    </svg>
                </button>
                <button id="map-style-colorful" type="button" title="Coloré"
                    class="w-10 h-10 flex items-center justify-center border-b border-slate-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 16 16">
                        <rect x="1" y="1" width="6" height="6" rx="1" fill="#F06292"/>
                        <rect x="9" y="1" width="6" height="6" rx="1" fill="#42A5F5"/>
                        <rect x="1" y="9" width="6" height="6" rx="1" fill="#FFA726"/>
                        <rect x="9" y="9" width="6" height="6" rx="1" fill="#66BB6A"/>
                    </svg>
                </button>
                <button id="map-style-google" type="button" title="Maps"
                    class="w-10 h-10 flex items-center justify-center transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    #world-map .leaflet-interactive:focus { outline: none; }
    #world-map { touch-action: pan-y; }
</style>

<script>
    const _mapEl = document.getElementById('world-map');
    if (_mapEl && !_mapEl._leaflet_id) {
        const countriesByIso = JSON.parse(
            document.getElementById('world-map-countries').textContent
        );
        const countriesById = JSON.parse(
            document.getElementById('world-map-coords').textContent
        );

        const AMERICA_SUBREGIONS = {
            nord:     new Set(['CAN', 'USA', 'MEX', 'GRL']),
            centrale: new Set(['BLZ', 'CRI', 'SLV', 'GTM', 'HND', 'NIC', 'PAN',
                               'CUB', 'HTI', 'DOM', 'JAM', 'TTO', 'ATG', 'BHS',
                               'BRB', 'DMA', 'GRD', 'KNA', 'VCT', 'LCA', 'ABW',
                               'CUW', 'PRI', 'GLP', 'MTQ', 'BLM', 'MAF']),
            sud:      new Set(['ARG', 'BOL', 'BRA', 'CHL', 'COL', 'ECU', 'GUY',
                               'GUF', 'PRY', 'PER', 'SUR', 'URY', 'VEN', 'FLK']),
        };

        const SUBREGION_VIEW = {
            nord:     { center: [50, -95],  zoom: 3 },
            centrale: { center: [17, -76],  zoom: 4 },
            sud:      { center: [-15, -58], zoom: 3 },
        };

        // continentId → {center, zoom}  (IDs from DB: 1=Afrique 2=Amerique 3=Asie 4=Europe 5=Oceanie)
        const CONTINENT_VIEW = {
            1: { center: [5,   20],  zoom: 3 },
            2: { center: [10, -80],  zoom: 3 },
            3: { center: [35,  90],  zoom: 3 },
            4: { center: [50,  15],  zoom: 4 },
            5: { center: [-25, 145], zoom: 3 },
        };

        let geojsonLayer = null;
        let selectedContinentId = null;
        let activeSubRegionSet = null;
        let selectedLayer = null;
        let isColorfulMode = false;
        let isGoogleMode = false;
        let markersLayer = null;
        let territoryMarker = null;

        function clearTerritoryMarker() {
            if (territoryMarker) {
                map.removeLayer(territoryMarker);
                territoryMarker = null;
            }
        }

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

        function isInActiveFilter(feature, country) {
            if (selectedContinentId !== null && (!country || country.continentId !== selectedContinentId)) {
                return false;
            }
            if (activeSubRegionSet !== null && !activeSubRegionSet.has(feature.id)) {
                return false;
            }
            return true;
        }

        const googleHoverStyle   = { fillColor: '#1a73e8', weight: 1.5, color: '#1a73e8', fillOpacity: 0.18, opacity: 0.6 };
        const googleSelectedStyle = { fillColor: '#1a73e8', weight: 2,   color: '#1a73e8', fillOpacity: 0.28, opacity: 0.9 };
        const googleInvisible     = { fillOpacity: 0, weight: 0, opacity: 0 };

        function styleForFeature(feature) {
            const country = countriesByIso[feature.id];
            if (isGoogleMode) {
                return googleInvisible;
            }
            if (isColorfulMode) {
                if (!country) {
                    return { fillColor: '#e2e8f0', weight: 0.5, color: '#ffffff', fillOpacity: 0.4, opacity: 0.4 };
                }
                if (!isInActiveFilter(feature, country)) {
                    return { fillColor: '#cbd5e1', weight: 0.5, color: '#ffffff', fillOpacity: 0.3, opacity: 0.4 };
                }
                return { fillColor: colorForFeature(feature.id), weight: 0.5, color: '#ffffff', fillOpacity: 1, opacity: 1 };
            }
            if (selectedContinentId === null) {
                return transparentStyle;
            }
            if (!country) return dimmedStyle;
            return isInActiveFilter(feature, country) ? defaultStyle : dimmedStyle;
        }

        function onEachFeature(feature, layer) {
            const country = countriesByIso[feature.id];

            layer.on({
                mouseover(e) {
                    if (!country) return;
                    if (!isInActiveFilter(feature, country)) return;
                    if (e.target === selectedLayer) return;
                    if (isGoogleMode) {
                        e.target.setStyle(googleHoverStyle);
                    } else if (isColorfulMode) {
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
                    if (isGoogleMode) {
                        e.target.setStyle(googleInvisible);
                        return;
                    }
                    geojsonLayer.resetStyle(e.target);
                    if (!isColorfulMode && selectedContinentId !== null) {
                        if (!isInActiveFilter(feature, country)) {
                            e.target.setStyle(dimmedStyle);
                        }
                    }
                },
                click(e) {
                    if (!country) return;
                    if (selectedLayer) {
                        if (isGoogleMode) {
                            selectedLayer.setStyle(googleInvisible);
                        } else {
                            geojsonLayer.resetStyle(selectedLayer);
                            const prevCountry = countriesByIso[selectedLayer.feature.id];
                            if (!isColorfulMode && selectedContinentId !== null && (!prevCountry || prevCountry.continentId !== selectedContinentId)) {
                                selectedLayer.setStyle(dimmedStyle);
                            }
                        }
                    }
                    const alreadySelected = selectedLayer === e.target;
                    selectedLayer = e.target;
                    e.target.setStyle(isGoogleMode ? googleSelectedStyle : selectedStyle);
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
            attributionControl: false,
        });
        const _attrCtrl = L.control.attribution({ position: 'bottomright' }).addTo(map);
        map.attributionControl = _attrCtrl;

        // --- Système de styles de carte ---
        const OSM_ATTR = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

        const MAP_STYLES = {
            satellite: {
                url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                attribution: OSM_ATTR,
            },
            light: {
                url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}',
                attribution: OSM_ATTR,
            },
            dark: {
                url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                attribution: OSM_ATTR,
                cssFilter: 'invert(1) hue-rotate(180deg) brightness(0.75) contrast(1.1)',
            },
            standard: {
                url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                attribution: OSM_ATTR,
            },
            colorful: {
                url: null,
                attribution: '',
            },
            google: {
                url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}',
                attribution: OSM_ATTR,
            },
        };

        const STYLE_KEY = 'mapworld-tile-style';
        let currentTileLayer = null;

        const redPinSvg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 36" width="20" height="30"><path fill="#EA4335" stroke="#B71C1C" stroke-width="0.5" d="M12 0C5.373 0 0 5.373 0 12c0 9 12 24 12 24S24 21 24 12C24 5.373 18.627 0 12 0z"/><circle fill="white" cx="12" cy="12" r="5"/></svg>`;
        const redIcon = L.divIcon({ className: '', html: redPinSvg, iconSize: [20, 30], iconAnchor: [10, 30], tooltipAnchor: [0, -30] });

        function buildMarkersLayer() {
            if (markersLayer) { map.removeLayer(markersLayer); markersLayer = null; }
            markersLayer = L.layerGroup();
            Object.entries(countriesById).forEach(([id, coords]) => {
                const numId = parseInt(id);
                const country = Object.values(countriesByIso).find(c => c.id === numId);
                if (!country) return;
                if (selectedContinentId !== null && country.continentId !== selectedContinentId) return;
                if (activeSubRegionSet !== null && !activeSubRegionSet.has(
                    Object.keys(countriesByIso).find(iso => countriesByIso[iso].id === numId)
                )) return;
                const marker = L.marker([coords.lat, coords.lng], { icon: redIcon, title: country.name });
                marker.on('click', () => {
                    $wire.$dispatch('country-selected', { countryId: numId, continentId: country.continentId });
                });
                marker.bindTooltip(country.name, { sticky: true });
                markersLayer.addLayer(marker);
            });
            markersLayer.addTo(map);
        }

        function applyMapStyle(styleKey) {
            const def = MAP_STYLES[styleKey] || MAP_STYLES.satellite;
            if (currentTileLayer) {
                map.removeLayer(currentTileLayer);
                currentTileLayer = null;
            }
            const cssFilter = def.cssFilter || '';
            if (def.url) {
                currentTileLayer = L.tileLayer(def.url, { attribution: def.attribution }).addTo(map);
                currentTileLayer.on('tileloadstart', () => {
                    map.getContainer().querySelectorAll('.leaflet-tile-pane').forEach(p => { p.style.filter = cssFilter; });
                });
            }
            map.getContainer().querySelectorAll('.leaflet-tile-pane').forEach(p => { p.style.filter = cssFilter; });

            isColorfulMode = styleKey === 'colorful';
            isGoogleMode = styleKey === 'google';
            localStorage.setItem(STYLE_KEY, styleKey);
            map.getContainer().style.background = isColorfulMode ? '#ffffff' : '';

            if (isGoogleMode) {
                buildMarkersLayer();
            } else {
                if (markersLayer) { map.removeLayer(markersLayer); markersLayer = null; }
            }

            if (!isColorfulMode) {
                const isLightTile = styleKey === 'light' || styleKey === 'standard' || styleKey === 'google';
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
                    btn.classList.add('bg-blue-600', 'text-white');
                } else {
                    btn.classList.remove('bg-blue-600', 'text-white');
                    btn.classList.add('bg-white', 'text-slate-700', 'hover:bg-slate-50');
                }
            });
        }

        applyMapStyle(localStorage.getItem(STYLE_KEY) || 'satellite');

        Object.keys(MAP_STYLES).forEach(key => {
            document.getElementById(`map-style-${key}`)?.addEventListener('click', () => applyMapStyle(key));
        });

        document.getElementById('map-zoom-in')?.addEventListener('click', () => map.zoomIn());
        document.getElementById('map-zoom-out')?.addEventListener('click', () => map.zoomOut());

        document.getElementById('map-reset-btn')?.addEventListener('click', () => {
            Livewire.dispatch('map-reset');
        });

        fetch('/geojson/world-countries.json?v={{ filemtime(public_path("geojson/world-countries.json")) }}')
            .then(r => r.json())
            .then(data => {
                geojsonLayer = L.geoJSON(data, {
                    style: styleForFeature,
                    onEachFeature,
                }).addTo(map);
            });

        Livewire.on('country-selected', ({ countryId }) => {
            if (!geojsonLayer) return;
            let layerFound = false;
            geojsonLayer.eachLayer(layer => {
                if (!layer.feature) return;
                const country = countriesByIso[layer.feature.id];
                if (!country || country.id !== countryId) return;

                layerFound = true;
                clearTerritoryMarker();

                if (selectedLayer && selectedLayer !== layer) {
                    if (isGoogleMode) {
                        selectedLayer.setStyle(googleInvisible);
                    } else {
                        geojsonLayer.resetStyle(selectedLayer);
                        const prevCountry = countriesByIso[selectedLayer.feature.id];
                        if (!isColorfulMode && selectedContinentId !== null && (!prevCountry || prevCountry.continentId !== selectedContinentId)) {
                            selectedLayer.setStyle(dimmedStyle);
                        }
                    }
                }

                const alreadySelected = selectedLayer === layer;
                selectedLayer = layer;
                layer.setStyle(isGoogleMode ? googleSelectedStyle : selectedStyle);
                layer.getElement()?.blur();

                if (!alreadySelected) {
                    try {
                        map.flyToBounds(layer.getBounds(), { maxZoom: 7, padding: [40, 40], duration: 0.8 });
                    } catch (e) {}
                }
            });

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
                    clearTerritoryMarker();
                    territoryMarker = L.circle([coords.lat, coords.lng], {
                        radius: 25000,
                        color: '#ffffff',
                        weight: 2,
                        fillColor: '#16a34a',
                        fillOpacity: 0.65,
                    }).addTo(map);
                }
            }
        });

        Livewire.on('continent-selected', ({ continentId, subRegion }) => {
            selectedContinentId = continentId ?? null;
            activeSubRegionSet = subRegion ? (AMERICA_SUBREGIONS[subRegion] ?? null) : null;
            selectedLayer = null;
            clearTerritoryMarker();

            const view = subRegion
                ? SUBREGION_VIEW[subRegion]
                : (selectedContinentId ? CONTINENT_VIEW[selectedContinentId] : null);
            if (view) {
                map.flyTo(view.center, view.zoom, { duration: 0.8 });
            } else {
                map.flyTo([20, 0], 2, { duration: 0.8 });
            }

            if (isGoogleMode) { buildMarkersLayer(); }

            if (!geojsonLayer) return;
            geojsonLayer.eachLayer(layer => {
                if (!layer.feature) return;
                layer.setStyle(styleForFeature(layer.feature));
            });
        });

        Livewire.on('map-reset', () => {
            activeSubRegionSet = null;
            map.flyTo([20, 0], 2, { duration: 0.8 });
            clearTerritoryMarker();
            if (selectedLayer && geojsonLayer) {
                if (isGoogleMode) {
                    selectedLayer.setStyle(googleInvisible);
                } else {
                    geojsonLayer.resetStyle(selectedLayer);
                    const prevCountry = countriesByIso[selectedLayer.feature.id];
                    if (!isColorfulMode && selectedContinentId !== null && (!prevCountry || prevCountry.continentId !== selectedContinentId)) {
                        selectedLayer.setStyle(dimmedStyle);
                    }
                }
                selectedLayer = null;
            }
            if (isGoogleMode) { buildMarkersLayer(); }
        });

        window.addEventListener('resize', () => map?.invalidateSize());
        window.addEventListener('map-mode-changed', (e) => {
            if (e.detail.mode === 'pays') { setTimeout(() => map?.invalidateSize(), 50); }
        });
    }
</script>
