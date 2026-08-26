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

    <div class="relative">
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

        <button
            id="map-reset-btn"
            type="button"
            class="hidden absolute top-3 right-3 z-[1000] flex items-center gap-1.5 bg-white/90 hover:bg-white text-slate-700 text-xs font-semibold px-3 py-1.5 rounded-lg shadow-md border border-slate-200 transition-colors"
            aria-label="Réinitialiser la carte — vue globale"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
            </svg>
            Vue globale
        </button>
    </div>
</div>

<style>
    #world-map .leaflet-interactive:focus { outline: none; }
</style>

<script>
    const countriesByIso = JSON.parse(
        document.getElementById('world-map-countries').textContent
    );
    const countriesById = JSON.parse(
        document.getElementById('world-map-coords').textContent
    );

    const resetBtn = document.getElementById('map-reset-btn');
    const showResetBtn = () => resetBtn?.classList.remove('hidden');
    const hideResetBtn = () => resetBtn?.classList.add('hidden');

    resetBtn?.addEventListener('click', () => Livewire.dispatch('map-reset'));

    let map = null;
    let geojsonLayer = null;
    let selectedContinentId = null;

    const defaultStyle  = { fillColor: '#4f46e5', weight: 1, color: '#ffffff', fillOpacity: 0.6 };
    const dimmedStyle   = { fillColor: '#94a3b8', weight: 0.5, color: '#cbd5e1', fillOpacity: 0.15 };
    const hoverStyle    = { fillColor: '#3730a3', weight: 2, color: '#ffffff', fillOpacity: 0.9 };
    const selectedStyle = { fillColor: '#16a34a', weight: 2, color: '#ffffff', fillOpacity: 0.9 };

    let selectedLayer = null;

    function styleForFeature(feature) {
        const country = countriesByIso[feature.id];
        if (selectedContinentId === null) {
            return country ? defaultStyle : dimmedStyle;
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
                showResetBtn();
                $wire.$dispatch('country-selected', { countryId: country.id, continentId: country.continentId });
            },
        });

        const tooltipName = country?.name ?? feature.properties?.name;
        if (tooltipName) {
            layer.bindTooltip(tooltipName, { sticky: true });
        }
    }

    map = L.map('world-map', {
        center: [20, 0],
        zoom: 2,
        minZoom: 1,
        maxZoom: 8,
        worldCopyJump: true,
        zoomControl: true,
    });

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

            // Reset previous selection
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

        showResetBtn();

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
                layer.setStyle(country ? defaultStyle : dimmedStyle);
            } else if (country && country.continentId === selectedContinentId) {
                layer.setStyle(defaultStyle);
            } else {
                layer.setStyle(dimmedStyle);
            }
        });
    });

    // Reset: fly back to world view, clear selection, hide button
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
        hideResetBtn();
    });

    window.addEventListener('resize', () => map?.invalidateSize());
</script>
