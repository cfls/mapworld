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

        return MarineArea::with(['parentArea', 'childAreas', 'coastalCountries'])
            ->find($this->marineAreaId);
    }
};
?>

<div aria-live="polite" aria-atomic="true" aria-label="Informations sur la zone marine sélectionnée">

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

        <div wire:key="marine-info-{{ $area->id }}" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest">INFO</h3>
            </div>

            <dl class="divide-y divide-slate-100 text-sm">

                {{-- Type --}}
                <div class="flex items-start gap-3 px-4 py-3">
                    <dt class="text-slate-500 shrink-0 w-28">Type</dt>
                    <dd class="text-slate-800 font-medium">{{ $typeLabel }}</dd>
                </div>

                {{-- Groupe océanique --}}
                @if ($area->ocean_group)
                    <div class="flex items-start gap-3 px-4 py-3">
                        <dt class="text-slate-500 shrink-0 w-28">Groupe</dt>
                        <dd class="text-slate-800 font-medium">{{ $area->ocean_group_label }}</dd>
                    </div>
                @endif

                {{-- Hiérarchie : parent --}}
                @if ($area->parentArea)
                    <div class="flex items-start gap-3 px-4 py-3">
                        <dt class="text-slate-500 shrink-0 w-28">Appartient à</dt>
                        <dd>
                            <button
                                wire:click="$dispatch('marine-area-selected', { marineAreaId: {{ $area->parentArea->id }} })"
                                class="text-sky-600 hover:text-sky-800 font-medium hover:underline transition-colors"
                            >
                                {{ $area->parentArea->name }}
                            </button>
                        </dd>
                    </div>
                @endif

                {{-- Sous-zones --}}
                @if ($area->childAreas->isNotEmpty())
                    <div class="flex items-start gap-3 px-4 py-3">
                        <dt class="text-slate-500 shrink-0 w-28">Sous-zones</dt>
                        <dd class="flex flex-wrap gap-1">
                            @foreach ($area->childAreas as $child)
                                <button
                                    wire:click="$dispatch('marine-area-selected', { marineAreaId: {{ $child->id }} })"
                                    class="text-xs px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 hover:bg-sky-100 transition-colors font-medium border border-sky-200"
                                >
                                    {{ $child->name }}
                                </button>
                            @endforeach
                        </dd>
                    </div>
                @endif

                {{-- Surface --}}
                @if ($area->surface_km2)
                    <div class="flex items-start gap-3 px-4 py-3">
                        <dt class="text-slate-500 shrink-0 w-28">Surface</dt>
                        <dd class="text-slate-800 font-medium">{{ $area->formatted_surface }}&thinsp;km²</dd>
                    </div>
                @endif

                {{-- Profondeur max --}}
                @if ($area->max_depth_m)
                    <div class="flex items-start gap-3 px-4 py-3">
                        <dt class="text-slate-500 shrink-0 w-28">Prof. max.</dt>
                        <dd class="text-slate-800 font-medium">{{ number_format($area->max_depth_m, 0, ',', "\u{00A0}") }}&thinsp;m</dd>
                    </div>
                @endif

                {{-- Pays côtiers --}}
                @if ($area->coastalCountries->isNotEmpty())
                    <div class="flex items-start gap-3 px-4 py-3">
                        <dt class="text-slate-500 shrink-0 w-28">Pays côtiers</dt>
                        <dd class="flex flex-wrap gap-1">
                            @foreach ($area->coastalCountries->sortBy('name') as $country)
                                <button
                                    @click="$dispatch('switch-to-pays', { countryId: {{ $country->id }} })"
                                    class="text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors font-medium border border-blue-200"
                                    title="Voir {{ $country->name }} dans la carte des pays"
                                >
                                    {{ $country->name }}
                                </button>
                            @endforeach
                        </dd>
                    </div>
                @endif

            </dl>
        </div>

    @endif

</div>
