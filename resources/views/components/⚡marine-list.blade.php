<?php

use App\Models\MarineArea;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url(as: 'zone')]
    public string $selectedAreaSlug = '';

    public string $search = '';

    public ?int $selectedAreaId = null;

    public ?string $selectedAreaName = null;

    public ?string $selectedOceanGroup = null;

    public function mount(): void
    {
        if ($this->selectedAreaSlug !== '') {
            $area = MarineArea::where('slug', $this->selectedAreaSlug)->first();
            if ($area) {
                $this->selectedAreaId = $area->id;
                $this->selectedAreaName = $area->name;
                $this->dispatch('marine-area-selected', marineAreaId: $area->id);
            }
        }
    }

    #[On('marine-area-selected')]
    public function syncSelectedArea(int $marineAreaId): void
    {
        $area = MarineArea::select('id', 'name', 'slug', 'ocean_group')->find($marineAreaId);
        $this->selectedAreaId = $marineAreaId;
        $this->selectedAreaName = $area?->name;
        $this->selectedAreaSlug = $area?->slug ?? '';
        $this->search = '';
    }

    #[On('ocean-group-selected')]
    public function filterByGroup(?string $group): void
    {
        $this->selectedOceanGroup = $group;
        $this->selectedAreaId = null;
        $this->selectedAreaName = null;
        $this->selectedAreaSlug = '';
        $this->search = '';
    }

    public function selectArea(int $areaId): void
    {
        $area = MarineArea::select('id', 'name', 'slug', 'ocean_group')->find($areaId);
        $this->selectedAreaId = $areaId;
        $this->selectedAreaName = $area?->name;
        $this->selectedAreaSlug = $area?->slug ?? '';
        $this->search = '';
        $this->dispatch('marine-area-selected', marineAreaId: $areaId);
    }

    #[Computed]
    public function areas()
    {
        if (strlen(trim($this->search)) < 1) {
            return collect();
        }

        return MarineArea::select('id', 'name', 'slug', 'type', 'ocean_group')
            ->whereHas('signVideos')
            ->when($this->selectedOceanGroup, fn ($q) => $q->where('ocean_group', $this->selectedOceanGroup))
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
    @ocean-group-selected.window="$wire.filterByGroup($event.detail?.group ?? null)"
    :class="isOpen ? 'pb-8' : ''"
    class="relative min-h-[56px]"
    role="search"
    aria-label="Rechercher une zone marine"
>
    {{-- Barre affichage (mode passif) --}}
    <button
        x-show="!isOpen"
        @click="openSearch()"
        type="button"
        class="w-full flex items-center gap-3 bg-white border border-slate-200 rounded-2xl px-4 sm:px-5 py-3 shadow-sm hover:border-sky-300 hover:shadow-md transition-all text-left min-h-[56px]"
        aria-label="{{ $selectedAreaName ? 'Zone sélectionnée : ' . $selectedAreaName . '. Cliquer pour changer.' : 'Rechercher une zone marine' }}"
    >
        @if ($selectedAreaName)
            <span class="text-xl" aria-hidden="true">🌊</span>
            <span class="text-lg sm:text-xl font-bold text-slate-900 uppercase tracking-wide truncate flex-1">
                {{ $selectedAreaName }}
            </span>
        @else
            <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span class="text-slate-400 text-base flex-1">
                Sélectionnez une zone sur la carte ou recherchez ici…
            </span>
        @endif

        <svg class="w-5 h-5 text-slate-300 shrink-0 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </button>

    {{-- Mode recherche --}}
    <div
        x-show="isOpen"
        x-cloak
        class="absolute left-0 right-0 top-0 bg-white border border-sky-400 rounded-2xl shadow-2xl overflow-hidden z-[600]"
    >
        <div class="flex items-center gap-3 px-4 sm:px-5 py-3 border-b border-slate-100">
            <svg class="w-5 h-5 text-sky-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
                x-ref="searchInput"
                type="text"
                wire:model.live.debounce.200ms="search"
                @input="focused = -1"
                placeholder="Rechercher un océan, une mer…"
                autocomplete="off"
                autocorrect="off"
                spellcheck="false"
                style="font-size: 16px;"
                class="flex-1 text-base text-slate-900 placeholder:text-slate-400 bg-transparent border-none outline-none min-w-0"
                aria-autocomplete="list"
                aria-controls="marine-search-results"
                aria-label="Rechercher une zone marine"
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

        <ul
            id="marine-search-results"
            role="listbox"
            class="max-h-64 overflow-y-auto divide-y divide-slate-50"
            aria-label="Zones marines correspondantes"
        >
            @forelse ($this->areas as $index => $area)
                @php
                    $typeIcon = match($area->type) {
                        'ocean' => '🌊',
                        'sea'   => '🌊',
                        'gulf'  => '🔵',
                        'bay'   => '🔵',
                        default => '💧',
                    };
                @endphp
                <li role="option">
                    <button
                        data-result
                        wire:click="selectArea({{ $area->id }})"
                        @click="closeSearch()"
                        :class="focused === {{ $index }} ? 'bg-sky-50' : 'hover:bg-slate-50'"
                        class="w-full flex items-center gap-3 px-4 sm:px-5 py-3 text-left transition-colors focus:outline-none focus:bg-sky-50 min-h-[44px]"
                        aria-label="{{ $area->name }}"
                    >
                        <span class="text-base shrink-0" aria-hidden="true">{{ $typeIcon }}</span>
                        <span class="text-sm font-medium text-slate-800">{{ $area->name }}</span>
                    </button>
                </li>
            @empty
                @if (strlen(trim($search)) >= 1)
                    <li class="px-5 py-5 text-sm text-slate-400 text-center" role="status">
                        Aucune zone marine trouvée
                    </li>
                @endif
            @endforelse
        </ul>
    </div>
</div>
