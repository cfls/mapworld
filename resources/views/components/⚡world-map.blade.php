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

        {{-- Zoom controls: HTML elements outside Leaflet, wire:ignore keeps them alive across re-renders --}}
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

        const defaultStyle     = { fillColor: '#4f46e5', weight: 0, fillOpacity: 0.35, opacity: 1 };
        const dimmedStyle      = { fillColor: '#94a3b8', weight: 0, fillOpacity: 0.08, opacity: 1 };
        const hoverStyle       = { fillColor: '#3730a3', weight: 0, fillOpacity: 0.55, opacity: 1 };
        const selectedStyle    = { fillColor: '#16a34a', weight: 0, fillOpacity: 0.65, opacity: 1 };
        const transparentStyle = { fillColor: '#000000', weight: 0,   color: '#000000', fillOpacity: 0,    opacity: 0 };

        function styleForFeature(feature) {
            const country = countriesByIso[feature.id];
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
                    e.target.setStyle(hoverStyle);
                },
                mouseout(e) {
                    if (e.target === selectedLayer) return;
                    geojsonLayer.resetStyle(e.target);
                    if (selectedContinentId !== null) {
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
                        if (selectedContinentId !== null && (!prevCountry || prevCountry.continentId !== selectedContinentId)) {
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

        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
        }).addTo(map);

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
                    if (selectedContinentId !== null && (!prevCountry || prevCountry.continentId !== selectedContinentId)) {
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
                    if (selectedContinentId !== null && (!prevCountry || prevCountry.continentId !== selectedContinentId)) {
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
                const country = countriesByIso[layer.feature.id];
                if (selectedContinentId === null) {
                    layer.setStyle(transparentStyle);
                } else if (country && country.continentId === selectedContinentId) {
                    layer.setStyle(defaultStyle);
                } else {
                    layer.setStyle(dimmedStyle);
                }
            });
        });

        // Reset: fly back to world view and clear selection
        Livewire.on('map-reset', () => {
            map.flyTo([20, 0], 2, { duration: 0.8 });
            if (selectedLayer && geojsonLayer) {
                geojsonLayer.resetStyle(selectedLayer);
                const prevCountry = countriesByIso[selectedLayer.feature.id];
                if (selectedContinentId !== null && (!prevCountry || prevCountry.continentId !== selectedContinentId)) {
                    selectedLayer.setStyle(dimmedStyle);
                }
                selectedLayer = null;
            }
        });

        window.addEventListener('resize', () => map?.invalidateSize());
    }
</script>
