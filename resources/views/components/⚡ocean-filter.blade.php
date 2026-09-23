<?php

use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?string $selectedGroup = null;

    #[On('marine-area-selected')]
    public function syncFromArea(int $marineAreaId): void
    {
        $area = \App\Models\MarineArea::select('ocean_group')->find($marineAreaId);
        $this->selectedGroup = $area?->ocean_group;
    }

    public function selectGroup(?string $group): void
    {
        $this->selectedGroup = $group;
        $this->dispatch('ocean-group-selected', group: $group);
    }
};
?>

<div
    role="group"
    aria-label="Filtrer par groupe océanique"
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
    @marine-area-selected.window="if ($event.detail?.marineAreaId) $wire.syncFromArea($event.detail.marineAreaId)"
>
    @php
        $groups = [
            'pacifique'   => 'Pacifique',
            // 'atlantique'  => 'Atlantique',
            // 'indien'      => 'Indien',
            'arctique'    => 'Arctique',
            // 'austral'     => 'Austral',
        ];
    @endphp

    <button
        wire:click="selectGroup(null)"
        aria-pressed="{{ $selectedGroup === null ? 'true' : 'false' }}"
        class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-1 whitespace-nowrap shrink-0 min-h-[36px]
               {{ $selectedGroup === null
                   ? 'bg-sky-600 text-white shadow-sm'
                   : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
    >
        Tous
    </button>

    @foreach ($groups as $key => $label)
        <button
            wire:click="selectGroup('{{ $key }}')"
            aria-pressed="{{ $selectedGroup === $key ? 'true' : 'false' }}"
            class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-1 whitespace-nowrap shrink-0 min-h-[36px]
                   {{ $selectedGroup === $key
                       ? 'bg-sky-600 text-white shadow-sm'
                       : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
        >
            {{ $label }}
        </button>
    @endforeach
</div>
