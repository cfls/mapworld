<?php

use App\Models\Country;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?int $countryId = null;

    #[On('country-selected')]
    public function selectCountry(int $countryId): void
    {
        $this->countryId = $countryId;
    }

    #[On('continent-selected')]
    public function syncContinent(): void
    {
        $this->countryId = null;
    }

    #[On('map-reset')]
    public function resetSelection(): void
    {
        $this->countryId = null;
    }

    #[Computed]
    public function country(): ?Country
    {
        if (! $this->countryId) {
            return null;
        }

        return Country::with(['lsfbVideos', 'internationalVideo', 'continent'])
            ->find($this->countryId);
    }
};
?>

<div
    aria-live="polite"
    aria-atomic="true"
    aria-label="Vidéos du pays sélectionné"
    x-data="{
        selectedContinent: null,
        selectedSubRegion: null,
        cloudBase: 'https://res.cloudinary.com/dmhdsjmzf/video/upload/',
        subRegionNames: {
            nord:     'Amérique du Nord',
            centrale: 'Amérique centrale',
            sud:      'Amérique du Sud',
        },
        continentVideos: {
            Amerique: {
                displayName: 'Amérique',
                type: 'regions',
                regions: [
                    { name: 'Amérique',          lsfb: 'Am%C3%A9rique_B_dlsrpt',          intl: 'Am%C3%A9rique_Int_jzbxji' },
                    { name: 'Amérique du Nord',  lsfb: 'Am%C3%A9rique_Du_Nord_B_wjp7cf',  intl: 'Am%C3%A9rique_Du_Nord_Int_q1ugjj' },
                    { name: 'Amérique centrale', lsfb: 'Am%C3%A9rique_Centrale_B_vggkqu', intl: 'Am%C3%A9rique_Centrale_Int_cyk6s7' },
                    { name: 'Amérique du Sud',   lsfb: 'Am%C3%A9rique_Du_Sud_B_smqinl',   intl: 'Am%C3%A9rique_Du_Sud_Int_mxrghg' },
                ]
            },
            Afrique: {
                displayName: 'Afrique',
                type: 'videos',
                lsfb: ['Afrique_1_B_qgde7p', 'Afrique_2_B_a913dj'],
                intl: 'Afrique_Int_vtyxe6'
            },
            Europe: {
                displayName: 'Europe',
                type: 'videos',
                lsfb: ['Europe_1_B_vgsepi', 'Europe_2_B_njsbjl'],
                intl: 'Europe_Int_yhlqp7'
            },
            Asie: {
                displayName: 'Asie',
                type: 'videos',
                lsfb: ['Asie_B_hftfqw'],
                intl: 'Asie_Int_ju4qdw'
            },
            Oceanie: {
                displayName: 'Océanie',
                type: 'videos',
                lsfb: ['Oc%C3%A9anie_B_i1zgb4'],
                intl: null
            }
        },
        lsfbCarouselIndex: 0,
        get currentContinent() {
            return this.selectedContinent ? this.continentVideos[this.selectedContinent] : null;
        },
        get filteredRegions() {
            const regions = this.currentContinent?.regions ?? [];
            if (!this.selectedSubRegion) return regions.slice(0, 1);
            const target = this.subRegionNames[this.selectedSubRegion];
            return target ? regions.filter(r => r.name === target) : regions;
        }
    }"
    x-on:continent-selected.window="
        const name = $event.detail.continentName;
        selectedContinent = continentVideos[name] ? name : null;
        selectedSubRegion = $event.detail.subRegion ?? null;
        lsfbCarouselIndex = 0;
    "
    x-on:country-selected.window="selectedContinent = null; selectedSubRegion = null;"
    x-on:map-reset.window="selectedContinent = null; selectedSubRegion = null;"
    class="flex flex-col gap-3 w-full lg:flex-1 lg:min-h-0"
>
    @if ($this->country)
        @php $continentName = $this->country->continent?->name ?? ''; @endphp
        <div wire:key="detail-{{ $this->country->id }}" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col lg:flex-1 lg:min-h-0">

            {{-- Header --}}
            <div class="bg-gradient-to-br from-blue-600 to-blue-500 px-5 py-4 shrink-0">
                <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-0.5">
                    PAYS{{ $continentName ? ' · ' . $continentName : '' }}
                </p>
                <h2 class="text-white text-2xl font-bold leading-tight flex items-center gap-2">
                    @if ($this->country->flag_path)
                        <img src="{{ asset($this->country->flag_path) }}" alt="" aria-hidden="true"
                             class="h-7 w-auto rounded shadow-sm shrink-0 object-cover">
                    @elseif ($this->country->iso2)
                        <img src="https://flagcdn.com/48x36/{{ strtolower($this->country->iso2) }}.png" alt="" aria-hidden="true"
                             class="h-7 w-auto rounded shadow-sm shrink-0">
                    @else
                        <span class="text-2xl" aria-hidden="true">🗺️</span>
                    @endif
                    <span>{{ $this->country->name }}</span>
                </h2>
            </div>

            {{-- Content --}}
            <div class="p-4 space-y-4 lg:overflow-y-auto lg:flex-1 lg:min-h-0">

                {{-- LSFB Card --}}
                @if ($this->country->lsfbVideos->isNotEmpty())
                    <section
                        aria-labelledby="lsfb-heading"
                        class="rounded-lg border border-slate-200 overflow-hidden"
                    >
                        <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0" aria-hidden="true"></span>
                            <h3 id="lsfb-heading" class="text-xs font-semibold text-slate-600 uppercase tracking-wide">LSFB</h3>
                        </div>

                        @php $lsfbCount = $this->country->lsfbVideos->count(); @endphp
                        <div
                            wire:key="lsfb-carousel-{{ $this->country->id }}"
                            x-data="{
                                total: {{ $lsfbCount }},
                                current: 0,
                                goTo(i) {
                                    this.$el.querySelectorAll('video').forEach(v => v.pause());
                                    this.current = i;
                                    this.$nextTick(() => this.$el.querySelectorAll('video')[i]?.play());
                                }
                            }"
                        >
                            @foreach ($this->country->lsfbVideos as $index => $lsfbVideo)
                                <div x-show="current === {{ $index }}" class="w-full">
                                    <x-video-player
                                        :url="$lsfbVideo->cloudinary_url"
                                        :thumbnail="$lsfbVideo->thumbnail_url"
                                        :label="'Vidéo LSFB ' . ($index + 1) . ' — ' . $this->country->name"
                                        :wire-key="'lsfb-carousel-video-' . $this->country->id . '-' . $index"
                                    />
                                </div>
                            @endforeach

                            <div x-show="total > 1" class="flex items-center justify-between px-4 py-2.5 bg-slate-50 border-t border-slate-200">
                                <button
                                    @click="goTo(0)"
                                    :disabled="current === 0"
                                    :class="current === 0 ? 'text-slate-300 cursor-default' : 'text-indigo-600 hover:text-indigo-800'"
                                    class="text-sm font-medium transition-colors"
                                    aria-label="Vidéo LSFB précédente"
                                >← Précédente</button>

                                <div class="flex gap-1.5" role="tablist" aria-label="Sélectionner une vidéo LSFB">
                                    @foreach ($this->country->lsfbVideos as $index => $lsfbVideo)
                                        <button
                                            @click="goTo({{ $index }})"
                                            :class="current === {{ $index }} ? 'bg-indigo-500 text-white' : 'bg-slate-200 text-slate-500 hover:bg-slate-300'"
                                            class="w-6 h-6 rounded-full text-xs font-bold transition-all duration-200 flex items-center justify-center"
                                            role="tab"
                                            :aria-selected="current === {{ $index }}"
                                            aria-label="Vidéo LSFB {{ $index + 1 }}"
                                        >{{ $index + 1 }}</button>
                                    @endforeach
                                </div>

                                <button
                                    @click="goTo(total - 1)"
                                    :disabled="current === total - 1"
                                    :class="current === total - 1 ? 'text-slate-300 cursor-default' : 'text-indigo-600 hover:text-indigo-800'"
                                    class="text-sm font-medium transition-colors"
                                    aria-label="Vidéo LSFB suivante"
                                >Suivante →</button>
                            </div>
                        </div>
                    </section>
                @endif

                {{-- Signes Internationaux Card --}}
                @if ($this->country->internationalVideo)
                    <section
                        aria-labelledby="intl-heading"
                        class="rounded-lg border border-slate-200 overflow-hidden"
                    >
                        <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-violet-500 shrink-0" aria-hidden="true"></span>
                            <h3 id="intl-heading" class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Signe International</h3>
                        </div>

                        <div class="w-full">
                            <x-video-player
                                :url="$this->country->internationalVideo->cloudinary_url"
                                :thumbnail="$this->country->internationalVideo->thumbnail_url"
                                :label="'Vidéo en Signes Internationaux — ' . $this->country->name"
                                :wire-key="'int-video-' . $this->country->id"
                            />
                        </div>
                    </section>
                @endif

                {{-- Aucune vidéo disponible --}}
                @if ($this->country->lsfbVideos->isEmpty() && ! $this->country->internationalVideo)
                    <div class="px-6 py-8 text-center text-slate-400 text-sm">
                        Aucune vidéo disponible pour ce pays.
                    </div>
                @endif

            </div>
        </div>

    @else
        {{-- Panel vidéos du continent sélectionné --}}
        <div x-show="selectedContinent" x-cloak class="flex flex-col gap-3 lg:flex-1 lg:overflow-y-auto lg:min-h-0">

            {{-- Régions (Amérique) --}}
            <div x-show="currentContinent?.type === 'regions'" class="flex flex-col gap-3">
                <template x-for="region in filteredRegions" :key="region.name">
                    <div class="flex flex-col gap-2">

                        {{-- LSFB --}}
                        <section class="rounded-lg border border-slate-200 overflow-hidden">
                            <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0" aria-hidden="true"></span>
                                <h3 class="text-xs font-semibold text-slate-600 uppercase tracking-wide">LSFB</h3>
                            </div>
                            <div class="relative aspect-video bg-slate-900">
                                <video
                                    class="absolute inset-0 w-full h-full object-contain"
                                    controls muted loop playsinline preload="metadata"
                                    :src="cloudBase + region.lsfb + '.mp4'"
                                    :aria-label="region.name + ' — LSFB'"
                                    x-init="$nextTick(() => { $el.load(); $el.play()?.catch(() => {}); })"
                                ></video>
                            </div>
                        </section>

                        {{-- Signe International --}}
                        <section x-show="region.intl" class="rounded-lg border border-slate-200 overflow-hidden">
                            <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-violet-500 shrink-0" aria-hidden="true"></span>
                                <h3 class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Signe International</h3>
                            </div>
                            <div class="relative aspect-video bg-slate-900">
                                <video
                                    class="absolute inset-0 w-full h-full object-contain"
                                    controls muted loop playsinline preload="metadata"
                                    :src="cloudBase + region.intl + '.mp4'"
                                    :aria-label="region.name + ' — Signes Internationaux'"
                                    x-init="$nextTick(() => { $el.load(); $el.play()?.catch(() => {}); })"
                                ></video>
                            </div>
                        </section>

                    </div>
                </template>
            </div>

            {{-- Vidéos directes (autres continents) --}}
            <div x-show="currentContinent?.type === 'videos'" class="flex flex-col gap-2">

                {{-- LSFB (carousel si hay más de uno) --}}
                <section class="rounded-lg border border-slate-200 overflow-hidden">
                    <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0" aria-hidden="true"></span>
                        <h3 class="text-xs font-semibold text-slate-600 uppercase tracking-wide">LSFB</h3>
                    </div>
                    <template x-for="(id, index) in (currentContinent?.lsfb ?? [])" :key="id">
                        <div x-show="lsfbCarouselIndex === index" class="relative aspect-video bg-slate-900">
                            <video
                                class="absolute inset-0 w-full h-full object-contain"
                                controls muted loop playsinline preload="metadata"
                                :src="cloudBase + id + '.mp4'"
                                :aria-label="currentContinent.displayName + ' — LSFB' + (currentContinent.lsfb.length > 1 ? ' ' + (index + 1) : '')"
                                x-effect="lsfbCarouselIndex === index ? ($el.load(), $el.play()?.catch(() => {})) : $el.pause()"
                            ></video>
                        </div>
                    </template>
                    <div
                        x-show="(currentContinent?.lsfb ?? []).length > 1"
                        class="flex items-center justify-between px-4 py-2.5 bg-slate-50 border-t border-slate-200"
                    >
                        <button
                            @click="lsfbCarouselIndex = Math.max(0, lsfbCarouselIndex - 1)"
                            :disabled="lsfbCarouselIndex === 0"
                            :class="lsfbCarouselIndex === 0 ? 'text-slate-300 cursor-default' : 'text-indigo-600 hover:text-indigo-800'"
                            class="text-sm font-medium transition-colors"
                            aria-label="Vidéo LSFB précédente"
                        >← Précédente</button>

                        <div class="flex gap-1.5">
                            <template x-for="(id, index) in (currentContinent?.lsfb ?? [])" :key="'dot-' + id">
                                <button
                                    @click="lsfbCarouselIndex = index"
                                    :class="lsfbCarouselIndex === index ? 'bg-indigo-500 text-white' : 'bg-slate-200 text-slate-500 hover:bg-slate-300'"
                                    class="w-6 h-6 rounded-full text-xs font-bold transition-all duration-200 flex items-center justify-center"
                                    :aria-label="'Vidéo LSFB ' + (index + 1)"
                                    x-text="index + 1"
                                ></button>
                            </template>
                        </div>

                        <button
                            @click="lsfbCarouselIndex = Math.min((currentContinent?.lsfb ?? []).length - 1, lsfbCarouselIndex + 1)"
                            :disabled="lsfbCarouselIndex === (currentContinent?.lsfb ?? []).length - 1"
                            :class="lsfbCarouselIndex === (currentContinent?.lsfb ?? []).length - 1 ? 'text-slate-300 cursor-default' : 'text-indigo-600 hover:text-indigo-800'"
                            class="text-sm font-medium transition-colors"
                            aria-label="Vidéo LSFB suivante"
                        >Suivante →</button>
                    </div>
                </section>

                {{-- Signe International --}}
                <section x-show="currentContinent?.intl" class="rounded-lg border border-slate-200 overflow-hidden">
                    <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-violet-500 shrink-0" aria-hidden="true"></span>
                        <h3 class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Signe International</h3>
                    </div>
                    <div class="relative aspect-video bg-slate-900">
                        <video
                            class="absolute inset-0 w-full h-full object-contain"
                            controls muted loop playsinline preload="metadata"
                            :src="currentContinent?.intl ? cloudBase + currentContinent.intl + '.mp4' : ''"
                            :aria-label="(currentContinent?.displayName ?? '') + ' — Signes Internationaux'"
                            x-effect="currentContinent?.intl ? ($el.load(), $el.play()?.catch(() => {})) : $el.pause()"
                        ></video>
                    </div>
                </section>

            </div>
        </div>

        {{-- État vide --}}
        <div
            x-show="!selectedContinent"
            class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center justify-center gap-3 min-h-[220px] px-8 text-center"
            role="status"
        >
            <svg class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            </svg>
            <div>
                <p class="text-slate-600 font-semibold text-sm">Sélectionnez un pays</p>
                <p class="text-slate-400 text-xs mt-1 leading-relaxed">
                    Cliquez sur la carte ou utilisez<br>la barre de recherche ci-dessus.
                </p>
            </div>
        </div>
    @endif
</div>
