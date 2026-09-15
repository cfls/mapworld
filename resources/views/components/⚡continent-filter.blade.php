<?php

use App\Models\Continent;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?int $selectedContinentId = null;

    public ?string $selectedSubRegion = null;

    #[On('country-selected')]
    public function syncFromCountry(int $countryId, ?int $continentId = null): void
    {
        $this->selectedContinentId = $continentId;
        $this->selectedSubRegion = null;
    }

    #[Computed]
    public function continents()
    {
        return Continent::orderBy('name')->get();
    }

    public function selectContinent(?int $continentId): void
    {
        $this->selectedContinentId = $continentId;
        $this->selectedSubRegion = null;
        $continent = $continentId ? Continent::find($continentId) : null;
        $this->dispatch('continent-selected', continentId: $continentId, continentName: $continent?->name, subRegion: null);
    }

    public function selectSubRegion(string $subRegion): void
    {
        $this->selectedSubRegion = $this->selectedSubRegion === $subRegion ? null : $subRegion;
        $continent = Continent::find($this->selectedContinentId);
        $this->dispatch('continent-selected',
            continentId: $this->selectedContinentId,
            continentName: $continent?->name,
            subRegion: $this->selectedSubRegion,
        );
    }
};
?>

<div class="flex flex-col gap-2">
    {{-- Fila principal de continentes --}}
    <div
        role="group"
        aria-label="Filtrer par continent"
        class="flex gap-2 overflow-x-auto scrollbar-none pb-0.5"
        x-data="{
            handleKey(e) {
                const btns = [...$el.querySelectorAll(':scope > button')];
                const idx = btns.indexOf(document.activeElement);
                if (e.key === 'ArrowRight' && idx < btns.length - 1) { e.preventDefault(); btns[idx + 1].focus(); }
                if (e.key === 'ArrowLeft'  && idx > 0)               { e.preventDefault(); btns[idx - 1].focus(); }
            }
        }"
        @keydown="handleKey"
    >
        <button
            wire:click="selectContinent(null)"
            aria-pressed="{{ $selectedContinentId === null ? 'true' : 'false' }}"
            class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-1 whitespace-nowrap shrink-0 min-h-[36px]
                   {{ $selectedContinentId === null
                       ? 'bg-blue-600 text-white shadow-sm'
                       : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
        >
            Tous
        </button>

        @foreach ($this->continents as $continent)
            <button
                wire:click="selectContinent({{ $continent->id }})"
                aria-pressed="{{ $selectedContinentId === $continent->id ? 'true' : 'false' }}"
                class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-1 whitespace-nowrap shrink-0 min-h-[36px]
                       {{ $selectedContinentId === $continent->id
                           ? 'bg-blue-600 text-white shadow-sm'
                           : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
            >
                {{ $continent->name }}
            </button>
        @endforeach
    </div>

    {{-- Sub-régions de l'Amérique --}}
    @php
        $selectedContinent = $selectedContinentId
            ? $this->continents->firstWhere('id', $selectedContinentId)
            : null;
    @endphp

    @if ($selectedContinent?->name === 'Amerique')
        <div
            role="group"
            aria-label="Filtrer par région d'Amérique"
            class="flex gap-2 overflow-x-auto scrollbar-none pb-0.5"
        >
            @foreach ([
                'nord'     => 'Amérique du Nord',
                'centrale' => 'Amérique Centrale',
                'sud'      => 'Amérique du Sud',
            ] as $key => $label)
                <button
                    wire:click="selectSubRegion('{{ $key }}')"
                    aria-pressed="{{ $selectedSubRegion === $key ? 'true' : 'false' }}"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 focus-visible:ring-offset-1 whitespace-nowrap shrink-0 min-h-[36px]
                           {{ $selectedSubRegion === $key
                               ? 'bg-indigo-500 text-white shadow-sm'
                               : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    @endif
</div>
