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

        return Country::with(['lsfbVideos', 'internationalVideo'])
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
        videoUrl: null,
        videoTitle: null,
        cloudBase: 'https://res.cloudinary.com/dmhdsjmzf/video/upload/',
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
        get currentContinent() {
            return this.selectedContinent ? this.continentVideos[this.selectedContinent] : null;
        },
        select(id, title) {
            this.videoUrl = this.cloudBase + id + '.mp4';
            this.videoTitle = title;
            this.$nextTick(() => this.$refs.continentVideo?.play());
        },
        clear() {
            this.$refs.continentVideo?.pause();
            this.videoUrl = null;
            this.videoTitle = null;
        }
    }"
    x-on:continent-selected.window="
        const name = $event.detail.continentName;
        selectedContinent = continentVideos[name] ? name : null;
        clear();
    "
    x-on:country-selected.window="selectedContinent = null; clear();"
    x-on:map-reset.window="selectedContinent = null; clear();"
    class="flex flex-col gap-3 lg:flex-1 lg:min-h-0"
>
    @if ($this->country)
        <div wire:key="detail-{{ $this->country->id }}" class="flex flex-col gap-3 lg:flex-1 lg:min-h-0">

            {{-- Carte LSFB --}}
            @if ($this->country->lsfbVideos->isNotEmpty())
                <section
                    aria-labelledby="lsfb-heading"
                    class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col lg:flex-1 lg:min-h-0"
                >
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center gap-2 shrink-0">
                        <span class="text-indigo-500 font-bold text-lg leading-none" aria-hidden="true">•</span>
                        <h3 id="lsfb-heading" class="text-sm font-semibold text-slate-700">LSFB</h3>
                    </div>

                    @if ($this->country->lsfbVideos->count() > 1)
                        <div
                            wire:key="lsfb-carousel-{{ $this->country->id }}"
                            x-data="{
                                current: 0,
                                goTo(i) {
                                    this.$el.querySelectorAll('video').forEach(v => v.pause());
                                    this.current = i;
                                    this.$nextTick(() => this.$el.querySelectorAll('video')[i]?.play());
                                }
                            }"
                            class="flex flex-col lg:flex-1 lg:min-h-0"
                        >
                            @foreach ($this->country->lsfbVideos as $index => $lsfbVideo)
                                <div x-show="current === {{ $index }}" class="lg:flex lg:flex-col lg:flex-1 lg:min-h-0">
                                    <x-video-player
                                        :url="$lsfbVideo->cloudinary_url"
                                        :thumbnail="$lsfbVideo->thumbnail_url"
                                        :label="'Vidéo LSFB ' . ($index + 1) . ' — ' . $this->country->name"
                                        :wire-key="'lsfb-carousel-video-' . $this->country->id . '-' . $index"
                                        :lg-fill-height="true"
                                    />
                                </div>
                            @endforeach

                            <div class="flex items-center justify-between px-4 py-2.5 bg-slate-50 border-t border-slate-100 shrink-0">
                                <button
                                    @click="goTo(0)"
                                    :disabled="current === 0"
                                    :class="current === 0 ? 'text-slate-300 cursor-default' : 'text-indigo-600 hover:text-indigo-800'"
                                    class="text-sm font-medium transition-colors min-h-[36px] px-2"
                                    aria-label="Vidéo LSFB précédente"
                                >← Précédente</button>

                                <div class="flex gap-1.5" role="tablist" aria-label="Sélectionner une vidéo LSFB">
                                    @foreach ($this->country->lsfbVideos as $index => $lsfbVideo)
                                        <button
                                            @click="goTo({{ $index }})"
                                            :class="current === {{ $index }} ? 'bg-indigo-500 text-white' : 'bg-slate-200 text-slate-500 hover:bg-slate-300'"
                                            class="w-7 h-7 rounded-full text-xs font-bold transition-all flex items-center justify-center"
                                            role="tab"
                                            :aria-selected="current === {{ $index }}"
                                            aria-label="Vidéo LSFB {{ $index + 1 }}"
                                        >{{ $index + 1 }}</button>
                                    @endforeach
                                </div>

                                <button
                                    @click="goTo({{ $this->country->lsfbVideos->count() - 1 }})"
                                    :disabled="current === {{ $this->country->lsfbVideos->count() - 1 }}"
                                    :class="current === {{ $this->country->lsfbVideos->count() - 1 }} ? 'text-slate-300 cursor-default' : 'text-indigo-600 hover:text-indigo-800'"
                                    class="text-sm font-medium transition-colors min-h-[36px] px-2"
                                    aria-label="Vidéo LSFB suivante"
                                >Suivante →</button>
                            </div>
                        </div>
                    @else
                        @php $lsfbVideo = $this->country->lsfbVideos->first(); @endphp
                        <x-video-player
                            :url="$lsfbVideo->cloudinary_url"
                            :thumbnail="$lsfbVideo->thumbnail_url"
                            :label="'Vidéo LSFB — ' . $this->country->name"
                            :wire-key="'lsfb-video-' . $this->country->id"
                            :lg-fill-height="true"
                        />
                    @endif
                </section>
            @endif

            {{-- Carte Signe International --}}
            @if ($this->country->internationalVideo)
                <section
                    aria-labelledby="intl-heading"
                    class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col lg:flex-1 lg:min-h-0"
                >
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center gap-2 shrink-0">
                        <span class="text-violet-500 font-bold text-lg leading-none" aria-hidden="true">•</span>
                        <h3 id="intl-heading" class="text-sm font-semibold text-slate-700">INT</h3>
                    </div>

                    <x-video-player
                        :url="$this->country->internationalVideo->cloudinary_url"
                        :thumbnail="$this->country->internationalVideo->thumbnail_url"
                        :label="'Vidéo en Signes Internationaux — ' . $this->country->name"
                        :wire-key="'int-video-' . $this->country->id"
                        :lg-fill-height="true"
                    />
                </section>
            @endif

            {{-- Aucune vidéo disponible --}}
            @if ($this->country->lsfbVideos->isEmpty() && ! $this->country->internationalVideo)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-6 py-8 text-center text-slate-400 text-sm">
                    Aucune vidéo disponible pour ce pays.
                </div>
            @endif

        </div>

    @else
        {{-- Panel vidéos du continent sélectionné --}}
        <div x-show="selectedContinent" x-cloak>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                <div class="px-4 py-3 border-b border-slate-100">
                    <p class="text-xs text-slate-400 uppercase tracking-wide mb-0.5">Continent</p>
                    <h2 class="text-base font-bold text-slate-800" x-text="currentContinent?.displayName"></h2>
                </div>

                {{-- Lecteur vidéo inline --}}
                <div x-show="videoUrl" x-cloak>
                    <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-600 truncate pr-2" x-text="videoTitle"></span>
                        <button
                            @click="clear()"
                            class="shrink-0 text-slate-400 hover:text-slate-700 transition-colors w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-200"
                            aria-label="Fermer la vidéo"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="relative aspect-video bg-slate-900">
                        <video
                            x-ref="continentVideo"
                            class="absolute inset-0 w-full h-full object-contain"
                            controls muted loop playsinline preload="metadata"
                            :src="videoUrl"
                            :aria-label="videoTitle"
                        ></video>
                    </div>
                </div>

                {{-- Régions (Amérique) --}}
                <div x-show="currentContinent?.type === 'regions'" class="p-3 space-y-2">
                    <template x-for="region in (currentContinent?.regions ?? [])" :key="region.name">
                        <div class="rounded-xl border border-slate-200 overflow-hidden">
                            <div class="px-3 py-2 bg-slate-50 border-b border-slate-100">
                                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide" x-text="region.name"></p>
                            </div>
                            <div class="flex gap-2 p-2.5">
                                <button
                                    @click="select(region.lsfb, region.name + ' — LSFB')"
                                    class="flex-1 px-3 py-2 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 min-h-[36px]"
                                    :aria-label="'Voir LSFB — ' + region.name"
                                >LSFB</button>
                                <button
                                    @click="select(region.intl, region.name + ' — Signes Internationaux')"
                                    class="flex-1 px-3 py-2 rounded-lg text-xs font-semibold bg-violet-50 text-violet-700 hover:bg-violet-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-500 min-h-[36px]"
                                    :aria-label="'Voir Signes Internationaux — ' + region.name"
                                >Signes Int.</button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Vidéos directes (autres continents) --}}
                <div x-show="currentContinent?.type === 'videos'" class="p-3 space-y-2">
                    <div class="rounded-xl border border-slate-200 overflow-hidden">
                        <div class="px-3 py-2 bg-slate-50 border-b border-slate-100 flex items-center gap-2">
                            <span class="text-indigo-500 font-bold" aria-hidden="true">•</span>
                            <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">LSFB</p>
                        </div>
                        <div class="flex gap-2 p-2.5">
                            <template x-for="(id, index) in (currentContinent?.lsfb ?? [])" :key="id">
                                <button
                                    @click="select(id, currentContinent.displayName + ' — LSFB' + (currentContinent.lsfb.length > 1 ? ' ' + (index + 1) : ''))"
                                    class="flex-1 px-3 py-2 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 min-h-[36px]"
                                    x-text="currentContinent.lsfb.length > 1 ? 'LSFB ' + (index + 1) : 'LSFB'"
                                ></button>
                            </template>
                        </div>
                    </div>

                    <div x-show="currentContinent?.intl" class="rounded-xl border border-slate-200 overflow-hidden">
                        <div class="px-3 py-2 bg-slate-50 border-b border-slate-100 flex items-center gap-2">
                            <span class="text-violet-500 font-bold" aria-hidden="true">•</span>
                            <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">INT</p>
                        </div>
                        <div class="flex gap-2 p-2.5">
                            <button
                                @click="currentContinent.intl && select(currentContinent.intl, currentContinent.displayName + ' — Signes Internationaux')"
                                class="flex-1 px-3 py-2 rounded-lg text-xs font-semibold bg-violet-50 text-violet-700 hover:bg-violet-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-500 min-h-[36px]"
                                :aria-label="'Voir Signes Internationaux — ' + currentContinent?.displayName"
                            >Signes Int.</button>
                        </div>
                    </div>
                </div>
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
