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
>
    @if ($this->country)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden h-full">

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
                            class="h-6 w-auto rounded-sm shadow-sm"
                        >
                    @elseif ($this->country->iso2)
                        <img
                            src="https://flagcdn.com/32x24/{{ strtolower($this->country->iso2) }}.png"
                            alt=""
                            aria-hidden="true"
                            class="h-6 w-auto rounded-sm shadow-sm"
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
                            <video
                                wire:key="lsfb-video-{{ $this->country->id }}"
                                class="w-full aspect-video bg-black"
                                controls
                                autoplay
                                muted
                                loop
                                preload="metadata"
                                aria-label="Vidéo LSFB — {{ $this->country->name }}"
                                @if ($lsfbVideo->thumbnail_url)
                                    poster="{{ $lsfbVideo->thumbnail_url }}"
                                @endif
                            >
                                <source src="{{ $lsfbVideo->cloudinary_url }}" type="video/mp4">
                            </video>
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
                        <h3 id="intl-heading" class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Signes Internationaux</h3>
                    </div>

                    @if ($this->country->internationalVideo)
                        <video
                            wire:key="int-video-{{ $this->country->id }}"
                            class="w-full aspect-video bg-black"
                            controls
                            autoplay
                            muted
                            loop
                            preload="metadata"
                            aria-label="Vidéo en Signes Internationaux — {{ $this->country->name }}"
                            @if ($this->country->internationalVideo->thumbnail_url)
                                poster="{{ $this->country->internationalVideo->thumbnail_url }}"
                            @endif
                        >
                            <source src="{{ $this->country->internationalVideo->cloudinary_url }}" type="video/mp4">
                        </video>
                    @else
                        <div
                            class="w-full aspect-video bg-slate-50 flex flex-col items-center justify-center gap-2"
                            role="img"
                            aria-label="Vidéo en Signes Internationaux pas encore disponible pour {{ $this->country->name }}"
                        >
                            <span class="text-3xl" aria-hidden="true">🌐</span>
                            <p class="text-xs text-slate-400 font-medium text-center px-4">
                                Vidéo en Signes Internationaux pas encore disponible
                            </p>
                        </div>
                    @endif
                </section>

            </div>
        </div>

    @else
        {{-- Empty state --}}
        <div
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

<script>
    Livewire.on('country-selected', () => {
        this.$el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
</script>
