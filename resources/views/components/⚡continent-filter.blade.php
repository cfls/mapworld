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

<div class="flex flex-wrap gap-2">
    <button
        wire:click="selectContinent(null)"
        class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors
               {{ $selectedContinentId === null
                   ? 'bg-indigo-600 text-white shadow-sm'
                   : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
    >
        Tous
    </button>

    @foreach ($this->continents as $continent)
        <button
            wire:click="selectContinent({{ $continent->id }})"
            class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors
                   {{ $selectedContinentId === $continent->id
                       ? 'bg-indigo-600 text-white shadow-sm'
                       : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
        >
            {{ $continent->name }}
        </button>
    @endforeach
</div>
