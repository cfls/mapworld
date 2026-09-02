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

        return Country::with(['continent', 'lsfbVideos', 'internationalVideo'])
            ->find($this->countryId);
    }
};
?>

<div
    aria-live="polite"
    aria-atomic="true"
    aria-label="Fiche du pays sélectionné"
    class="scroll-mt-16"
    x-data="{ showAmerique: false }"
    x-on:continent-selected.window="showAmerique = ($event.detail.continentName === 'Amerique')"
    x-on:country-selected.window="showAmerique = false"
    x-on:map-reset.window="showAmerique = false"
>
    @if ($this->country)
        <div wire:key="state-country-{{ $this->country->id }}" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden h-full">

            {{-- Header --}}
            <div class="bg-gradient-to-br from-indigo-600 to-indigo-500 px-5 py-4">
                <p class="text-indigo-200 text-xs font-semibold uppercase tracking-widest mb-0.5">
                    {{ $this->country->continent->name }}
                </p>
                <h2 class="text-white text-2xl font-bold leading-tight flex items-center gap-2">
                    @if ($this->country->flag_path)
                        <img
                            src="{{ asset($this->country->flag_path) }}"
                            alt=""
                            aria-hidden="true"
                            class="h-10 w-auto rounded shadow-sm"
                        >
                    @elseif ($this->country->iso2)
                        <img
                            src="https://flagcdn.com/64x48/{{ strtolower($this->country->iso2) }}.png"
                            alt=""
                            aria-hidden="true"
                            class="h-10 w-auto rounded shadow-sm"
                        >
                    @endif
                    <span>{{ $this->country->name }}</span>
                </h2>
                @if ($this->country->iso3)
                    <span class="inline-block mt-1 text-xs font-mono text-indigo-300 bg-indigo-700/40 px-2 py-0.5 rounded">
                        {{ $this->country->iso3 }}
                    </span>
                @endif
            </div>

            <div class="p-4 space-y-4">

                {{-- LSFB Card (solo si hay videos) --}}
                @if ($this->country->lsfbVideos->isNotEmpty())
                <section
                    aria-labelledby="lsfb-heading"
                    class="rounded-lg border border-slate-200 overflow-hidden"
                >
                    <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0" aria-hidden="true"></span>
                        <h3 id="lsfb-heading" class="text-xs font-semibold text-slate-600 uppercase tracking-wide">LSFB</h3>
                    </div>

                    @if ($this->country->lsfbVideos->count() > 1)
                            {{-- Carousel for 2 videos --}}
                            <div
                                wire:key="lsfb-carousel-{{ $this->country->id }}"
                                x-data="{
                                    current: 0,
                                    goTo(i) {
                                        this.$el.querySelectorAll('video').forEach(v => v.pause());
                                        this.current = i;
                                        this.$nextTick(() => this.$el.querySelectorAll('video')[i].play());
                                    }
                                }"
                                x-cloak
                            >
                                @foreach ($this->country->lsfbVideos as $index => $lsfbVideo)
                                    <div x-show="current === {{ $index }}">
                                        <video
                                            class="w-full aspect-video bg-black"
                                            controls
                                            autoplay
                                            muted
                                            loop
                                            preload="metadata"
                                            aria-label="Vidéo LSFB {{ $index + 1 }} — {{ $this->country->name }}"
                                            @if ($lsfbVideo->thumbnail_url)
                                                poster="{{ $lsfbVideo->thumbnail_url }}"
                                            @endif
                                        >
                                            <source src="{{ $lsfbVideo->cloudinary_url }}" type="video/mp4">
                                        </video>
                                    </div>
                                @endforeach

                                {{-- Carousel controls --}}
                                <div class="flex items-center justify-between px-4 py-2.5 bg-slate-50 border-t border-slate-200">
                                    <button
                                        @click="goTo(0)"
                                        :disabled="current === 0"
                                        :class="current === 0 ? 'text-slate-300 cursor-default' : 'text-indigo-600 hover:text-indigo-800'"
                                        class="text-sm font-medium transition-colors"
                                        aria-label="Vidéo LSFB précédente"
                                    >
                                        ← Précédente
                                    </button>

                                    <div class="flex gap-1.5" role="tablist" aria-label="Sélectionner une vidéo LSFB">
                                        @foreach ($this->country->lsfbVideos as $index => $lsfbVideo)
                                            <button
                                                @click="goTo({{ $index }})"
                                                :class="current === {{ $index }} ? 'bg-indigo-500 w-4' : 'bg-slate-300 w-2'"
                                                class="h-2 rounded-full transition-all duration-200"
                                                role="tab"
                                                :aria-selected="current === {{ $index }}"
                                                aria-label="Vidéo LSFB {{ $index + 1 }}"
                                            ></button>
                                        @endforeach
                                    </div>

                                    <button
                                        @click="goTo(1)"
                                        :disabled="current === 1"
                                        :class="current === 1 ? 'text-slate-300 cursor-default' : 'text-indigo-600 hover:text-indigo-800'"
                                        class="text-sm font-medium transition-colors"
                                        aria-label="Vidéo LSFB suivante"
                                    >
                                        Suivante →
                                    </button>
                                </div>
                            </div>
                        @else
                            {{-- Single video, no carousel --}}
                            @php $lsfbVideo = $this->country->lsfbVideos->first(); @endphp
                            <div class="relative" x-data="{ frozen: false }">
                                <video
                                    x-ref="video"
                                    wire:key="lsfb-video-{{ $this->country->id }}"
                                    class="w-full aspect-video bg-black"
                                    controls
                                    autoplay
                                    muted
                                    loop
                                    preload="metadata"
                                    aria-label="Vidéo LSFB — {{ $this->country->name }}"
                                    x-on:stalled="frozen = true"
                                    x-on:error="frozen = true"
                                    x-on:playing="frozen = false"
                                    @if ($lsfbVideo->thumbnail_url)
                                        poster="{{ $lsfbVideo->thumbnail_url }}"
                                    @endif
                                >
                                    <source src="{{ $lsfbVideo->cloudinary_url }}" type="video/mp4">
                                </video>
                                <div
                                    x-show="frozen"
                                    x-cloak
                                    class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center gap-3"
                                >
                                    <svg class="w-8 h-8 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                    </svg>
                                    <button
                                        @click="frozen = false; $refs.video.load(); $refs.video.play();"
                                        class="px-4 py-2 rounded-lg bg-white text-slate-800 text-sm font-semibold hover:bg-slate-100 transition-colors"
                                    >
                                        Relancer la vidéo
                                    </button>
                                </div>
                            </div>
                        @endif
                </section>
                @endif

                {{-- Signes Internationaux Card --}}
                <section
                    aria-labelledby="intl-heading"
                    class="rounded-lg border border-slate-200 overflow-hidden"
                >
                    <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-violet-500 shrink-0" aria-hidden="true"></span>
                        <h3 id="intl-heading" class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Signe International</h3>
                    </div>

                    @if ($this->country->internationalVideo)
                        <div class="relative" x-data="{ frozen: false }">
                            <video
                                x-ref="video"
                                wire:key="int-video-{{ $this->country->id }}"
                                class="w-full aspect-video bg-black"
                                controls
                                autoplay
                                muted
                                loop
                                preload="metadata"
                                aria-label="Vidéo en Signes Internationaux — {{ $this->country->name }}"
                                x-on:stalled="frozen = true"
                                x-on:error="frozen = true"
                                x-on:playing="frozen = false"
                                @if ($this->country->internationalVideo->thumbnail_url)
                                    poster="{{ $this->country->internationalVideo->thumbnail_url }}"
                                @endif
                            >
                                <source src="{{ $this->country->internationalVideo->cloudinary_url }}" type="video/mp4">
                            </video>
                            <div
                                x-show="frozen"
                                x-cloak
                                class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center gap-3"
                            >
                                <svg class="w-8 h-8 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                </svg>
                                <button
                                    @click="frozen = false; $refs.video.load(); $refs.video.play();"
                                    class="px-4 py-2 rounded-lg bg-white text-slate-800 text-sm font-semibold hover:bg-slate-100 transition-colors"
                                >
                                    Relancer la vidéo
                                </button>
                            </div>
                        </div>
                    @else
                        <div
                            class="w-full aspect-video bg-slate-50 flex flex-col items-center justify-center gap-2"
                            role="img"
                            aria-label="Vidéo en Signe International pas encore disponible pour {{ $this->country->name }}"
                        >
                            <span class="text-3xl" aria-hidden="true">🌐</span>
                            <p class="text-xs text-slate-400 font-medium text-center px-4">
                                Vidéo en Signe International pas encore disponible
                            </p>
                        </div>
                    @endif
                </section>

            </div>
        </div>

    @else
        {{-- Vidéos régionales Amérique: siempre en el DOM, visible/oculto por Alpine --}}
        <div x-show="showAmerique" x-cloak>
            <div
                class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden"
                x-data="{
                    videoUrl: null,
                    videoTitle: null,
                    cloudBase: 'https://res.cloudinary.com/dmhdsjmzf/video/upload/',
                    regions: [
                        { name: 'Amérique',          lsfb: 'Am%C3%A9rique_B_dlsrpt',          intl: 'Am%C3%A9rique_Int_jzbxji' },
                        { name: 'Amérique du Nord',  lsfb: 'Am%C3%A9rique_Du_Nord_B_wjp7cf',  intl: 'Am%C3%A9rique_Du_Nord_Int_q1ugjj' },
                        { name: 'Amérique centrale', lsfb: 'Am%C3%A9rique_Centrale_B_vggkqu', intl: 'Am%C3%A9rique_Centrale_Int_cyk6s7' },
                        { name: 'Amérique du Sud',   lsfb: 'Am%C3%A9rique_Du_Sud_B_smqinl',   intl: 'Am%C3%A9rique_Du_Sud_Int_mxrghg' },
                    ],
                    select(url, title) {
                        this.videoUrl = url;
                        this.videoTitle = title;
                        this.$nextTick(() => this.$refs.regionVideo?.play());
                    },
                    clear() {
                        this.$refs.regionVideo?.pause();
                        this.videoUrl = null;
                        this.videoTitle = null;
                    }
                }"
            >
                {{-- Header --}}
                <div class="bg-gradient-to-br from-indigo-600 to-indigo-500 px-5 py-4">
                    <p class="text-indigo-200 text-xs font-semibold uppercase tracking-widest mb-0.5">Continent</p>
                    <h2 class="text-white text-2xl font-bold leading-tight">Amérique</h2>
                    <p class="text-indigo-300 text-xs mt-1">Vidéos régionales</p>
                </div>

                {{-- Lecteur vidéo inline --}}
                <div x-show="videoUrl" x-cloak>
                    <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-600 truncate pr-2" x-text="videoTitle"></span>
                        <button
                            @click="clear()"
                            class="shrink-0 text-slate-400 hover:text-slate-700 transition-colors w-6 h-6 flex items-center justify-center rounded-full hover:bg-slate-100"
                            aria-label="Fermer la vidéo"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <video
                        x-ref="regionVideo"
                        class="w-full aspect-video bg-black"
                        controls
                        muted
                        loop
                        preload="metadata"
                        :src="videoUrl"
                        :aria-label="videoTitle"
                    ></video>
                </div>

                {{-- Cards de región --}}
                <div class="p-4 space-y-2">
                    <template x-for="region in regions" :key="region.name">
                        <div class="rounded-lg border border-slate-200 overflow-hidden">
                            <div class="px-4 py-2 bg-slate-50 border-b border-slate-200">
                                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide" x-text="region.name"></p>
                            </div>
                            <div class="flex gap-2 p-2.5">
                                <button
                                    @click="select(cloudBase + region.lsfb + '.mp4', region.name + ' — LSFB')"
                                    class="flex-1 px-3 py-2 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                                    :aria-label="'Voir LSFB — ' + region.name"
                                >
                                    LSFB
                                </button>
                                <button
                                    @click="select(cloudBase + region.intl + '.mp4', region.name + ' — Signes Internationaux')"
                                    class="flex-1 px-3 py-2 rounded-lg text-xs font-semibold bg-violet-50 text-violet-700 hover:bg-violet-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-500"
                                    :aria-label="'Voir Signes Internationaux — ' + region.name"
                                >
                                    Signes Int.
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Empty state --}}
        <div
            x-show="!showAmerique"
            class="bg-white rounded-xl shadow-sm border border-slate-200 flex flex-col items-center justify-center gap-3 h-full min-h-[280px] px-8 text-center"
            role="status"
        >
            <span class="text-5xl opacity-60" aria-hidden="true">🗺️</span>
            <div>
                <p class="text-slate-600 font-semibold">Sélectionnez un pays</p>
                <p class="text-slate-400 text-sm mt-1 leading-relaxed">
                    Cliquez sur un pays dans la carte<br>ou dans la liste ci-dessous.
                </p>
            </div>
        </div>
    @endif
</div>

@script
<script>
    $wire.on('country-selected', () => {
        $el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
</script>
@endscript
