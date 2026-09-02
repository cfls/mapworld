<?php

use App\Models\Country;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?int $selectedContinentId = null;

    public string $search = '';

    #[On('continent-selected')]
    public function filterByContinent(?int $continentId): void
    {
        $this->selectedContinentId = $continentId;
        $this->search = '';
    }

    #[Computed]
    public function countries()
    {
        return Country::when(
            $this->selectedContinentId,
            fn ($q) => $q->where('continent_id', $this->selectedContinentId)
        )
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();
    }
};
?>

<div>
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-3">
        <div class="flex items-center gap-2">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">
                Pays
            </h2>
            <span
                class="text-xs text-slate-400 font-medium"
                aria-live="polite"
                aria-atomic="true"
            >
                {{ $this->countries->count() }} résultat{{ $this->countries->count() !== 1 ? 's' : '' }}
            </span>
        </div>

        <div role="search" class="sm:ml-auto">
            <label for="country-search" class="sr-only">Rechercher un pays</label>
            <input
                id="country-search"
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher un pays…"
                autocomplete="off"
                class="w-full sm:w-56 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >
        </div>
    </div>

    <ul
        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-1.5"
        aria-label="Liste des pays"
    >
        @forelse ($this->countries as $country)
            <li>
                <button
                    wire:click="$dispatch('country-selected', { countryId: {{ $country->id }}, continentId: {{ $country->continent_id }} })"
                    class="w-full text-left px-3 py-2 text-sm text-slate-700 hover:bg-indigo-50
                           hover:text-indigo-700 rounded-lg transition-colors truncate focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                    title="{{ $country->name }}"
                >
                    {{ $country->name }}
                </button>
            </li>
        @empty
            <li class="col-span-full py-6 text-center text-sm text-slate-400">
                Aucun pays trouvé pour « {{ $search }} ».
            </li>
        @endforelse
    </ul>
</div>

@script
<script>
    window.addEventListener('country-selected', () => {
        document.getElementById('map-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
</script>
@endscript
