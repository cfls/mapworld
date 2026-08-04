<?php

use App\Models\Country;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function countriesByIso(): array
    {
        return Country::select('id', 'iso_code', 'continent_id', 'name')
            ->get()
            ->keyBy('iso_code')
            ->map(fn ($c) => ['id' => $c->id, 'continentId' => $c->continent_id, 'name' => $c->name])
            ->all();
    }
};
?>

<div>
    {{-- JSON data carrier: read once by the script on mount --}}
    <script type="application/json" id="world-map-countries">{!! json_encode($this->countriesByIso) !!}</script>

    <p class="sr-only">
        Carte mondiale interactive. Cliquez sur un pays pour afficher ses vidéos en langue des signes.
        Vous pouvez également utiliser la barre de recherche et la liste des pays ci-dessous.
    </p>

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
</div>

<style>
    #world-map .leaflet-interactive:focus { outline: none; }
</style>

<script>
    const countriesByIso = JSON.parse(
        document.getElementById('world-map-countries').textContent
    );

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
                selectedLayer = e.target;
                e.target.setStyle(selectedStyle);
                e.target.getElement()?.blur();
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
        geojsonLayer.eachLayer(layer => {
            if (!layer.feature) return;
            const country = countriesByIso[layer.feature.id];
            if (!country || country.id !== countryId) return;

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

    window.addEventListener('resize', () => map?.invalidateSize());
</script>
