<?php

use App\Models\MarineArea;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function marineAreasByGeoJsonId(): array
    {
        return MarineArea::active()
            ->select('id', 'name', 'geojson_id', 'type', 'ocean_group')
            ->whereNotNull('geojson_id')
            ->get()
            ->keyBy('geojson_id')
            ->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'type' => $a->type, 'ocean_group' => $a->ocean_group])
            ->all();
    }

    /** Maps ocean_group → [geojson_id, ...] for ALL areas (active or not), used for group bounds. */
    #[Computed]
    public function oceanGroupGeojsonIds(): array
    {
        return MarineArea::query()
            ->select('ocean_group', 'geojson_id')
            ->whereNotNull('geojson_id')
            ->whereNotNull('ocean_group')
            ->get()
            ->groupBy('ocean_group')
            ->map(fn ($items) => $items->pluck('geojson_id')->values()->all())
            ->all();
    }

    #[Computed]
    public function marineAreasWithCenter(): array
    {
        return MarineArea::active()
            ->select('id', 'name', 'ocean_group', 'center_lat', 'center_lng', 'center_zoom', 'linked_geojson_ids')
            ->whereNull('geojson_id')
            ->where(fn ($q) => $q->whereNotNull('center_lat')->orWhereNotNull('linked_geojson_ids'))
            ->get()
            ->map(fn ($a) => [
                'id'               => $a->id,
                'name'             => $a->name,
                'ocean_group'      => $a->ocean_group,
                'lat'              => $a->center_lat ? (float) $a->center_lat : null,
                'lng'              => $a->center_lng ? (float) $a->center_lng : null,
                'zoom'             => $a->center_zoom ?? 4,
                'linked_ne_ids'    => $a->linked_geojson_ids ?? [],
            ])
            ->keyBy('id')
            ->all();
    }
};
?>

<div>
    <script type="application/json" id="ocean-map-areas">{!! json_encode($this->marineAreasByGeoJsonId) !!}</script>
    <script type="application/json" id="ocean-map-centers">{!! json_encode($this->marineAreasWithCenter) !!}</script>
    <script type="application/json" id="ocean-group-geojson-ids">{!! json_encode($this->oceanGroupGeojsonIds) !!}</script>

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
                   h-[42vh]
                   sm:h-[45vh]
                   md:h-[420px]
                   lg:h-[520px]
                   xl:h-[600px]"
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
    #ocean-map { touch-action: pan-y; }

    .ocean-label {
        background: transparent;
        border: none;
        box-shadow: none;
        padding: 0;
        font-size: 11px;
        font-weight: 600;
        color: #fff;
        text-shadow:
            0 0 3px rgba(0,0,0,0.9),
            0 0 6px rgba(0,0,0,0.6),
            1px 1px 2px rgba(0,0,0,0.8);
        white-space: nowrap;
        pointer-events: none;
        letter-spacing: 0.02em;
    }
    .ocean-label::before { display: none; }

    @-webkit-keyframes ocean-pulse {
        0%   { box-shadow: 0 0 0 0 rgba(12,74,110,0.5); }
        70%  { box-shadow: 0 0 0 10px rgba(12,74,110,0); }
        100% { box-shadow: 0 0 0 0 rgba(12,74,110,0); }
    }
    @keyframes ocean-pulse {
        0%   { box-shadow: 0 0 0 0 rgba(12,74,110,0.5); }
        70%  { box-shadow: 0 0 0 10px rgba(12,74,110,0); }
        100% { box-shadow: 0 0 0 0 rgba(12,74,110,0); }
    }
</style>

<script>
    const _oceanMapEl = document.getElementById('ocean-map');
    if (_oceanMapEl && !_oceanMapEl._leaflet_id) {
        const marineAreasByGeoJsonId = JSON.parse(
            document.getElementById('ocean-map-areas').textContent
        );
        const marineAreasWithCenter = JSON.parse(
            document.getElementById('ocean-map-centers').textContent
        );
        const oceanGroupGeojsonIds = JSON.parse(
            document.getElementById('ocean-group-geojson-ids').textContent
        );

        let geojsonLayer = null;
        let selectedLayers = [];
        let selectedAreaId = null;
        let centerMarker = null;

        const defaultStyle  = { fillColor: '#0284c7', weight: 1,   color: '#ffffff', fillOpacity: 0.25, opacity: 0.5 };
        const hoverStyle    = { fillColor: '#0369a1', weight: 1.5, color: '#ffffff', fillOpacity: 0.45, opacity: 0.8 };
        const selectedStyle = { fillColor: '#0c4a6e', weight: 1.5, color: '#ffffff', fillOpacity: 0.65, opacity: 0.9 };
        const dimmedStyle   = { fillColor: '#94a3b8', weight: 0.5, color: '#ffffff', fillOpacity: 0.08, opacity: 0.2 };
        const unknownStyle  = { fillColor: '#94a3b8', weight: 0.5, color: '#ffffff', fillOpacity: 0.05, opacity: 0.2 };

        let activeOceanGroup = null;

        function styleForFeature(feature) {
            const neId = String(feature.properties?.ne_id ?? '');
            const area = marineAreasByGeoJsonId[neId];
            if (!area) { return unknownStyle; }
            if (activeOceanGroup && area.ocean_group !== activeOceanGroup) { return dimmedStyle; }
            return defaultStyle;
        }

        function clearSelectedLayers() {
            selectedLayers.forEach(l => geojsonLayer?.resetStyle(l));
            selectedLayers = [];
        }

        function applyGroupFilter(group) {
            activeOceanGroup = group;
            clearSelectedLayers();
            clearCenterMarker();
            selectedAreaId = null;
            if (!geojsonLayer) { return; }

            const groupLayers = [];
            geojsonLayer.eachLayer(layer => {
                const neId = String(layer.feature?.properties?.ne_id ?? '');
                const area = marineAreasByGeoJsonId[neId];
                if (!area) { layer.setStyle(unknownStyle); return; }
                if (group && area.ocean_group !== group) {
                    layer.setStyle(dimmedStyle);
                } else {
                    layer.setStyle(defaultStyle);
                    if (group) { groupLayers.push(layer); }
                }
            });

            if (!group) {
                map.flyTo([20, 0], 2, { duration: 0.8 });
                return;
            }

            // Prefer active polygon layers; fall back to all DB polygons for the group
            const boundsLayers = groupLayers.length > 0
                ? groupLayers
                : (() => {
                    const fallbackIds = new Set(oceanGroupGeojsonIds[group] ?? []);
                    const layers = [];
                    geojsonLayer?.eachLayer(layer => {
                        const neId = String(layer.feature?.properties?.ne_id ?? '');
                        if (fallbackIds.has(neId)) { layers.push(layer); }
                    });
                    return layers;
                })();

            if (boundsLayers.length > 0) {
                try {
                    let bounds = null;
                    boundsLayers.forEach(layer => {
                        const b = layer.getBounds();
                        bounds = bounds ? bounds.extend(b) : b;
                    });
                    // Polygons crossing the antimeridian produce world-spanning bounds — use group center instead
                    const lngSpan = bounds ? bounds.getEast() - bounds.getWest() : 0;
                    if (bounds?.isValid() && lngSpan < 350) {
                        map.flyToBounds(bounds, { maxZoom: 4, padding: [30, 30], duration: 0.8 });
                        return;
                    }
                } catch (_) {}
            }

            // Fallback: fly to hardcoded group center (for antimeridian-crossing oceans)
            const GROUP_CENTERS = {
                pacifique:  { lat:   5, lng: -160, zoom: 2 },
                atlantique: { lat:  20, lng:  -30, zoom: 3 },
                indien:     { lat: -20, lng:   75, zoom: 3 },
                arctique:   { lat:  82, lng:    0, zoom: 3 },
                austral:    { lat: -65, lng:    0, zoom: 3 },
            };
            const gc = GROUP_CENTERS[group];
            if (gc) { map.flyTo([gc.lat, gc.lng], gc.zoom, { duration: 0.8 }); }
        }

        window.addEventListener('ocean-group-selected', e => applyGroupFilter(e.detail.group));

        function onEachFeature(feature, layer) {
            const neId = String(feature.properties?.ne_id ?? '');
            const area = marineAreasByGeoJsonId[neId];
            const label = feature.properties?.name_fr ?? feature.properties?.name ?? '';

            layer.on({
                mouseover(e) {
                    if (!area) return;
                    if (selectedLayers.includes(e.target)) return;
                    e.target.setStyle(hoverStyle);
                },
                mouseout(e) {
                    if (selectedLayers.includes(e.target)) return;
                    geojsonLayer.resetStyle(e.target);
                    if (!area) e.target.setStyle(unknownStyle);
                },
                click(e) {
                    if (!area) return;
                    clearSelectedLayers();
                    clearCenterMarker();
                    selectedLayers = [e.target];
                    selectedAreaId = area.id;
                    e.target.setStyle(selectedStyle);
                    e.target.getElement()?.blur();
                    try {
                        map.flyToBounds(e.target.getBounds(), { maxZoom: 5, padding: [40, 40], duration: 0.8 });
                    } catch (_) {}
                    window.dispatchEvent(new CustomEvent('marine-area-selected', { detail: { marineAreaId: area.id } }));
                },
            });

            if (area) layer.bindTooltip(area.name, { permanent: true, direction: 'center', className: 'ocean-label' });
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

        const centerLabelMarkers = [];

        function addCenterLabels() {
            Object.values(marineAreasWithCenter).forEach(area => {
                if (area.lat === null || area.linked_ne_ids?.length) { return; }

                const icon = L.divIcon({ className: '', html: '', iconSize: [0, 0] });
                const marker = L.marker([area.lat, area.lng], { icon, interactive: false })
                    .bindTooltip(area.name, { permanent: true, direction: 'center', className: 'ocean-label' })
                    .addTo(map);

                // Permanent tooltips are in the DOM immediately — make them clickable
                const tooltipEl = marker.getTooltip()?.getElement();
                if (tooltipEl) {
                    tooltipEl.style.pointerEvents = 'auto';
                    tooltipEl.style.cursor = 'pointer';
                    tooltipEl.addEventListener('click', (e) => {
                        e.stopPropagation();
                        window.dispatchEvent(new CustomEvent('marine-area-selected', { detail: { marineAreaId: area.id } }));
                    });
                }

                centerLabelMarkers.push(marker);
            });
        }

        fetch('/geojson/world-oceans.json?v={{ filemtime(public_path("geojson/world-oceans.json")) }}')
            .then(r => r.json())
            .then(data => {
                geojsonLayer = L.geoJSON(data, {
                    style: styleForFeature,
                    onEachFeature,
                }).addTo(map);
                addCenterLabels();
            });

        function clearCenterMarker() {
            if (centerMarker) { map.removeLayer(centerMarker); centerMarker = null; }
        }

        window.addEventListener('marine-area-selected', (e) => {
            const marineAreaId = e.detail?.marineAreaId;
            if (!marineAreaId || marineAreaId === selectedAreaId) { return; }

            clearSelectedLayers();
            clearCenterMarker();
            selectedAreaId = marineAreaId;

            // Case 1: single polygon match via geojson_id
            let targetNeId = null;
            for (const [neId, area] of Object.entries(marineAreasByGeoJsonId)) {
                if (area.id === marineAreaId) { targetNeId = neId; break; }
            }

            if (targetNeId && geojsonLayer) {
                geojsonLayer.eachLayer(layer => {
                    if (String(layer.feature?.properties?.ne_id ?? '') !== targetNeId) { return; }
                    selectedLayers.push(layer);
                    layer.setStyle(selectedStyle);
                });
                if (selectedLayers.length) {
                    try { map.flyToBounds(selectedLayers[0].getBounds(), { maxZoom: 5, padding: [40, 40], duration: 0.8 }); } catch (_) {}
                }
                return;
            }

            // Case 2: area without polygon — center marker or linked polygons
            const centerArea = marineAreasWithCenter[marineAreaId];
            if (!centerArea) { return; }

            const linkedIds = centerArea.linked_ne_ids ?? [];

            if (linkedIds.length && geojsonLayer) {
                // Highlight all linked polygons and fly to their combined bounds
                let bounds = null;
                geojsonLayer.eachLayer(layer => {
                    const neId = String(layer.feature?.properties?.ne_id ?? '');
                    if (!linkedIds.includes(neId)) { return; }
                    selectedLayers.push(layer);
                    layer.setStyle(selectedStyle);
                    try {
                        const b = layer.getBounds();
                        bounds = bounds ? bounds.extend(b) : b;
                    } catch (_) {}
                });
                if (bounds?.isValid()) {
                    map.flyToBounds(bounds, { maxZoom: 4, padding: [40, 40], duration: 0.9 });
                }
                return;
            }

            // Case 3: center-point marker fallback
            if (centerArea.lat === null) { return; }

            map.flyTo([centerArea.lat, centerArea.lng], centerArea.zoom, { duration: 0.9 });

            const pulseIcon = L.divIcon({
                className: '',
                html: '<div style="width:18px;height:18px;border-radius:50%;background:rgba(12,74,110,0.7);border:2px solid #fff;box-shadow:0 0 0 4px rgba(12,74,110,0.3);-webkit-animation:ocean-pulse 1.5s infinite;animation:ocean-pulse 1.5s infinite;"></div>',
                iconSize: [18, 18],
                iconAnchor: [9, 9],
            });
            centerMarker = L.marker([centerArea.lat, centerArea.lng], { icon: pulseIcon })
                .bindTooltip(centerArea.name, { permanent: false, sticky: true })
                .addTo(map);
        });

        window.addEventListener('resize', () => map?.invalidateSize());
        window.addEventListener('map-mode-changed', (e) => {
            if (e.detail.mode === 'mers') { setTimeout(() => map?.invalidateSize(), 50); }
            if (e.detail.mode !== 'mers') { clearCenterMarker(); selectedAreaId = null; }
        });
    }
</script>
