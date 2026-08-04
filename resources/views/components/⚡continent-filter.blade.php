<?php

use App\Models\Continent;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public ?int $selectedContinentId = null;

    #[Computed]
    public function continents()
    {
        return Continent::orderBy('name')->get();
    }

    public function selectContinent(?int $continentId): void
    {
        $this->selectedContinentId = $continentId;
        $this->dispatch('continent-selected', continentId: $continentId);
    }
};
?>

<div
    role="group"
    aria-label="Filtrer par continent"
    class="flex flex-wrap gap-2"
    x-data="{
        handleKey(e) {
            const btns = [...$el.querySelectorAll('button')];
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
        class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-1
               {{ $selectedContinentId === null
                   ? 'bg-indigo-600 text-white shadow-sm'
                   : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
    >
        Tous
    </button>

    @foreach ($this->continents as $continent)
        <button
            wire:click="selectContinent({{ $continent->id }})"
            aria-pressed="{{ $selectedContinentId === $continent->id ? 'true' : 'false' }}"
            class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-1
                   {{ $selectedContinentId === $continent->id
                       ? 'bg-indigo-600 text-white shadow-sm'
                       : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
        >
            {{ $continent->name }}
        </button>
    @endforeach
</div>
