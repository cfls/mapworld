<?php

use App\Models\Country;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?int $selectedContinentId = null;

    public string $search = '';

    public ?int $selectedCountryId = null;

    public ?string $selectedCountryName = null;

    public ?string $selectedCountryIso2 = null;

    public ?string $selectedCountryFlag = null;

    /**
     * Called when the map or another component selects a country.
     * Updates the bar display without re-dispatching (avoids loop).
     */
    #[On('country-selected')]
    public function syncSelectedCountry(int $countryId, ?int $continentId = null): void
    {
        $country = Country::select('id', 'name', 'iso2', 'flag_path')->find($countryId);
        $this->selectedCountryId = $countryId;
        $this->selectedCountryName = $country?->name;
        $this->selectedCountryIso2 = $country?->iso2;
        $this->selectedCountryFlag = $country?->flag_path;
        $this->search = '';
    }

    #[On('continent-selected')]
    public function filterByContinent(?int $continentId): void
    {
        $this->selectedContinentId = $continentId;
        $this->selectedCountryId = null;
        $this->selectedCountryName = null;
        $this->selectedCountryIso2 = null;
        $this->selectedCountryFlag = null;
        $this->search = '';
    }

    #[On('map-reset')]
    public function resetSelection(): void
    {
        $this->selectedCountryId = null;
        $this->selectedCountryName = null;
        $this->selectedCountryIso2 = null;
        $this->selectedCountryFlag = null;
        $this->search = '';
    }

    /**
     * Select a country from the dropdown: update own state and dispatch for other components.
     * The component does not receive its own dispatched event, preventing a double update.
     */
    public function selectCountry(int $countryId, int $continentId): void
    {
        $country = Country::select('id', 'name', 'iso2', 'flag_path')->find($countryId);
        $this->selectedCountryId = $countryId;
        $this->selectedCountryName = $country?->name;
        $this->selectedCountryIso2 = $country?->iso2;
        $this->selectedCountryFlag = $country?->flag_path;
        $this->search = '';
        $this->dispatch('country-selected', countryId: $countryId, continentId: $continentId);
    }

    #[Computed]
    public function countries()
    {
        if (strlen(trim($this->search)) < 1) {
            return collect();
        }

        return Country::select('id', 'name', 'iso2', 'flag_path', 'continent_id')
            ->whereHas('signVideos')
            ->when($this->selectedContinentId, fn ($q) => $q->where('continent_id', $this->selectedContinentId))
            ->where('name', 'like', "%{$this->search}%")
            ->orderBy('name')
            ->limit(12)
            ->get();
    }
};
?>

<div
    x-data="{
        isOpen: false,
        focused: -1,
        openSearch() {
            this.isOpen = true;
            this.focused = -1;
            this.$nextTick(() => this.$refs.searchInput?.focus());
        },
        closeSearch() {
            this.isOpen = false;
            this.focused = -1;
        },
        navigate(e) {
            if (!this.isOpen) return;
            const items = [...this.$el.querySelectorAll('[data-result]')];
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.focused = Math.min(this.focused + 1, items.length - 1);
                items[this.focused]?.scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.focused = Math.max(this.focused - 1, 0);
                items[this.focused]?.scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter' && this.focused >= 0) {
                e.preventDefault();
                items[this.focused]?.click();
            } else if (e.key === 'Escape') {
                this.closeSearch();
            }
        }
    }"
    @click.outside="closeSearch()"
    @keydown="navigate"
    :class="isOpen ? 'pb-8' : ''"
    class="relative min-h-[56px]"
    role="search"
    aria-label="Rechercher un pays"
>
    {{-- Barre affichage (mode passif) --}}
    <button
        x-show="!isOpen"
        @click="openSearch()"
        type="button"
        class="w-full flex items-center gap-3 bg-white border border-slate-200 rounded-2xl px-4 sm:px-5 py-3 shadow-sm hover:border-blue-300 hover:shadow-md transition-all text-left min-h-[56px]"
        aria-label="{{ $selectedCountryName ? 'Pays sélectionné : ' . $selectedCountryName . '. Cliquer pour changer.' : 'Rechercher un pays' }}"
    >
        @if ($selectedCountryName)
            {{-- Drapeau --}}
            @if ($selectedCountryFlag)
                <img src="{{ asset($selectedCountryFlag) }}" alt="" aria-hidden="true"
                     class="h-8 w-auto rounded shadow-sm shrink-0 object-cover">
            @elseif ($selectedCountryIso2)
                <img src="https://flagcdn.com/48x36/{{ strtolower($selectedCountryIso2) }}.png" alt="" aria-hidden="true"
                     class="h-8 w-auto rounded shadow-sm shrink-0">
            @else
                <span class="w-10 h-7 bg-slate-200 rounded shrink-0" aria-hidden="true"></span>
            @endif

            <span class="text-lg sm:text-xl font-bold text-slate-900 uppercase tracking-wide truncate flex-1">
                {{ $selectedCountryName }}
            </span>
        @else
            <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span class="text-slate-400 text-base flex-1">
                Sélectionnez un pays sur la carte ou recherchez ici…
            </span>
        @endif

        {{-- Icône loupe à droite --}}
        <svg class="w-5 h-5 text-slate-300 shrink-0 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </button>

    {{-- Mode recherche --}}
    <div
        x-show="isOpen"
        x-cloak
        class="absolute left-0 right-0 top-0 bg-white border border-blue-400 rounded-2xl shadow-2xl overflow-hidden z-[600]"
    >
        {{-- Champ de saisie --}}
        <div class="flex items-center gap-3 px-4 sm:px-5 py-3 border-b border-slate-100">
            <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
                x-ref="searchInput"
                type="text"
                wire:model.live.debounce.200ms="search"
                @input="focused = -1"
                placeholder="Rechercher un pays…"
                autocomplete="off"
                autocorrect="off"
                spellcheck="false"
                style="font-size: 16px;"
                class="flex-1 text-base text-slate-900 placeholder:text-slate-400 bg-transparent border-none outline-none min-w-0"
                aria-autocomplete="list"
                aria-controls="search-results"
                aria-label="Rechercher un pays"
            >
            <button
                @click="closeSearch()"
                type="button"
                class="shrink-0 text-slate-400 hover:text-slate-700 transition-colors w-9 h-9 flex items-center justify-center rounded-xl hover:bg-slate-100"
                aria-label="Fermer la recherche"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Résultats --}}
        <ul
            id="search-results"
            role="listbox"
            class="max-h-64 overflow-y-auto divide-y divide-slate-50"
            aria-label="Pays correspondants"
        >
            @forelse ($this->countries as $index => $country)
                <li role="option">
                    <button
                        data-result
                        wire:click="selectCountry({{ $country->id }}, {{ $country->continent_id }})"
                        @click="closeSearch()"
                        :class="focused === {{ $index }} ? 'bg-blue-50' : 'hover:bg-slate-50'"
                        class="w-full flex items-center gap-3 px-4 sm:px-5 py-3 text-left transition-colors focus:outline-none focus:bg-blue-50 min-h-[44px]"
                        aria-label="{{ $country->name }}"
                    >
                        @if ($country->flag_path)
                            <img src="{{ asset($country->flag_path) }}" alt="" aria-hidden="true"
                                 class="h-5 w-auto rounded-sm shrink-0 object-cover">
                        @elseif ($country->iso2)
                            <img src="https://flagcdn.com/32x24/{{ strtolower($country->iso2) }}.png" alt="" aria-hidden="true"
                                 class="h-5 w-auto rounded-sm shrink-0">
                        @else
                            <span class="w-7 h-5 bg-slate-200 rounded-sm shrink-0" aria-hidden="true"></span>
                        @endif
                        <span class="text-sm font-medium text-slate-800">{{ $country->name }}</span>
                    </button>
                </li>
            @empty
                @if (strlen(trim($search)) >= 1)
                    <li class="px-5 py-5 text-sm text-slate-400 text-center" role="status">
                        Aucun pays trouvé
                    </li>
                @endif
            @endforelse
        </ul>
    </div>
</div>
