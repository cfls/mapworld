<?php

use App\Models\MarineArea;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?int $marineAreaId = null;

    #[On('marine-area-selected')]
    public function selectMarineArea(int $marineAreaId): void
    {
        $this->marineAreaId = $marineAreaId;
    }

    #[Computed]
    public function marineArea(): ?MarineArea
    {
        if (! $this->marineAreaId) {
            return null;
        }

        return MarineArea::with(['lsfbVideos', 'internationalVideo'])
            ->find($this->marineAreaId);
    }
};
?>

<div aria-live="polite" aria-atomic="true" aria-label="Fiche de la zone marine sélectionnée" class="scroll-mt-16">

    @if ($this->marineArea)
        @php
            $area = $this->marineArea;
            $typeLabel = match($area->type) {
                'ocean' => 'Océan',
                'sea'   => 'Mer',
                'gulf'  => 'Golfe',
                'bay'   => 'Baie',
                default => 'Zone marine',
            };
        @endphp
        <div wire:key="state-area-{{ $area->id }}" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden h-full">

            {{-- Header --}}
            <div class="bg-gradient-to-br from-sky-600 to-sky-500 px-5 py-4">
                <p class="text-sky-200 text-xs font-semibold uppercase tracking-widest mb-0.5">{{ $typeLabel }}</p>
                <h2 class="text-white text-2xl font-bold leading-tight flex items-center gap-2">
                    <span class="text-2xl" aria-hidden="true">🌊</span>
                    <span>{{ $area->name }}</span>
                </h2>
            </div>

            <div class="p-4 space-y-4">

                {{-- LSFB --}}
                <section aria-labelledby="ocean-lsfb-heading" class="rounded-lg border border-slate-200 overflow-hidden">
                    <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0" aria-hidden="true"></span>
                        <h3 id="ocean-lsfb-heading" class="text-xs font-semibold text-slate-600 uppercase tracking-wide">LSFB</h3>
                    </div>

                    @if ($area->lsfbVideos->isEmpty())
                        <div
                            class="w-full aspect-video bg-slate-50 flex flex-col items-center justify-center gap-2"
                            role="img"
                            aria-label="Vidéo LSFB pas encore disponible pour {{ $area->name }}"
                        >
                            <span class="text-3xl" aria-hidden="true">🤟</span>
                            <p class="text-xs text-slate-400 font-medium text-center px-4">
                                Vidéo LSFB pas encore disponible
                            </p>
                        </div>
                    @elseif ($area->lsfbVideos->count() > 1)
                        <div
                            wire:key="ocean-lsfb-carousel-{{ $area->id }}"
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
                            @foreach ($area->lsfbVideos as $index => $video)
                                <div x-show="current === {{ $index }}">
                                    <x-video-player
                                        :url="$video->cloudinary_url"
                                        :thumbnail="$video->thumbnail_url"
                                        :label="'Vidéo LSFB ' . ($index + 1) . ' — ' . $area->name"
                                        :wire-key="'ocean-lsfb-carousel-' . $area->id . '-' . $index"
                                    />
                                </div>
                            @endforeach
                            <div class="flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-50 border-t border-slate-200">
                                @foreach ($area->lsfbVideos as $index => $video)
                                    <button
                                        @click="goTo({{ $index }})"
                                        :class="current === {{ $index }} ? 'bg-indigo-500 text-white' : 'bg-slate-200 text-slate-500 hover:bg-slate-300'"
                                        class="w-6 h-6 rounded-full text-xs font-bold transition-all duration-200 flex items-center justify-center"
                                        aria-label="Vidéo LSFB {{ $index + 1 }}"
                                    >{{ $index + 1 }}</button>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <x-video-player
                            :url="$area->lsfbVideos->first()->cloudinary_url"
                            :thumbnail="$area->lsfbVideos->first()->thumbnail_url"
                            :label="'Vidéo LSFB — ' . $area->name"
                            :wire-key="'ocean-lsfb-' . $area->id"
                        />
                    @endif
                </section>

                {{-- Signes Internationaux --}}
                <section aria-labelledby="ocean-intl-heading" class="rounded-lg border border-slate-200 overflow-hidden">
                    <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-violet-500 shrink-0" aria-hidden="true"></span>
                        <h3 id="ocean-intl-heading" class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Signe International</h3>
                    </div>

                    @if ($area->internationalVideo)
                        <x-video-player
                            :url="$area->internationalVideo->cloudinary_url"
                            :thumbnail="$area->internationalVideo->thumbnail_url"
                            :label="'Vidéo en Signes Internationaux — ' . $area->name"
                            :wire-key="'ocean-intl-' . $area->id"
                        />
                    @else
                        <div
                            class="w-full aspect-video bg-slate-50 flex flex-col items-center justify-center gap-2"
                            role="img"
                            aria-label="Vidéo en Signe International pas encore disponible pour {{ $area->name }}"
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
        <div
            class="bg-white rounded-xl shadow-sm border border-slate-200 flex flex-col items-center justify-center gap-3 h-full min-h-[280px] px-8 text-center"
            role="status"
        >
            <span class="text-5xl opacity-60" aria-hidden="true">🌊</span>
            <div>
                <p class="text-slate-600 font-semibold">Sélectionnez une zone marine</p>
                <p class="text-slate-400 text-sm mt-1 leading-relaxed">
                    Cliquez sur un océan ou une mer dans la carte.
                </p>
            </div>
        </div>
    @endif

</div>

@script
<script>
    $wire.on('marine-area-selected', () => {
        $el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
</script>
@endscript
