<?php

use App\Models\Country;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?int $selectedContinentId = null;

    #[On('continent-selected')]
    public function filterByContinent(?int $continentId): void
    {
        $this->selectedContinentId = $continentId;
    }

    #[Computed]
    public function countries()
    {
        return Country::when(
            $this->selectedContinentId,
            fn ($q) => $q->where('continent_id', $this->selectedContinentId)
        )
            ->orderBy('name')
            ->get();
    }
};
?>

<div>
    <div class="flex items-center gap-2 mb-3">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">
            Pays
        </h2>
        <span class="text-xs text-slate-400 font-medium">
            {{ $this->countries->count() }} résultats
        </span>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-1.5">
        @foreach ($this->countries as $country)
            <button
                wire:click="$dispatch('country-selected', { countryId: {{ $country->id }} })"
                class="text-left px-3 py-2 text-sm text-slate-700 hover:bg-indigo-50
                       hover:text-indigo-700 rounded-lg transition-colors truncate"
                title="{{ $country->name }}"
            >
                {{ $country->name }}
            </button>
        @endforeach
    </div>
</div>
