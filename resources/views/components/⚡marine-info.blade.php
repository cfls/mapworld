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

    #[On('ocean-group-selected')]
    public function selectByGroup(?string $group): void
    {
        if (! $group) {
            $this->marineAreaId = null;

            return;
        }

        $oceans = MarineArea::active()
            ->where('ocean_group', $group)
            ->where('type', 'ocean')
            ->get();

        $this->marineAreaId = $oceans->count() === 1 ? $oceans->first()->id : null;
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

        <section
            wire:key="marine-info-{{ $area->id }}"
            aria-labelledby="marine-info-heading"
            class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden"
        >
            <div class="px-5 py-3 border-b border-slate-100 flex items-center gap-2">
                <span class="text-emerald-500 font-bold text-lg leading-none" aria-hidden="true">•</span>
                <h3 id="marine-info-heading" class="text-base font-bold text-slate-800">INFO</h3>
            </div>

            <dl class="px-5 py-4 grid grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-3">

                {{-- Type --}}
                <div class="flex items-baseline gap-2 min-w-0">
                    <dt class="text-sm font-bold text-slate-700 uppercase tracking-wide shrink-0 w-28">Type</dt>
                    <dd class="text-sm text-slate-800">{{ $typeLabel }}</dd>
                </div>

                {{-- Groupe océanique --}}
                @if ($area->ocean_group)
                    <div class="flex items-baseline gap-2 min-w-0">
                        <dt class="text-sm font-bold text-slate-700 uppercase tracking-wide shrink-0 w-28">Groupe</dt>
                        <dd class="text-sm text-slate-800">{{ $area->ocean_group_label }}</dd>
                    </div>
                @endif

                {{-- Hiérarchie : parent --}}
                @if ($area->parentArea)
                    <div class="flex items-baseline gap-2 min-w-0">
                        <dt class="text-sm font-bold text-slate-700 uppercase tracking-wide shrink-0 w-28">Appartient à</dt>
                        <dd class="text-sm text-slate-800">{{ $area->parentArea->name }}</dd>
                    </div>
                @endif

                {{-- Surface --}}
                @if ($area->surface_km2)
                    <div class="flex items-baseline gap-2 min-w-0">
                        <dt class="text-sm font-bold text-slate-700 uppercase tracking-wide shrink-0 w-28">Surface</dt>
                        <dd class="text-sm text-slate-800">{{ $area->formatted_surface }}&thinsp;km²</dd>
                    </div>
                @endif

                {{-- Profondeur max --}}
                @if ($area->max_depth_m)
                    <div class="flex items-baseline gap-2 min-w-0">
                        <dt class="text-sm font-bold text-slate-700 uppercase tracking-wide shrink-0 w-28">Prof. max.</dt>
                        <dd class="text-sm text-slate-800">{{ number_format($area->max_depth_m, 0, ',', "\u{00A0}") }}&thinsp;m</dd>
                    </div>
                @endif

                {{-- Sous-zones --}}
                @if ($area->childAreas->isNotEmpty())
                    <div class="flex items-baseline gap-2 min-w-0 sm:col-span-2">
                        <dt class="text-sm font-bold text-slate-700 uppercase tracking-wide shrink-0 w-28">Sous-zones</dt>
                        <dd class="text-sm text-slate-800">{{ $area->childAreas->pluck('name')->join(', ') }}</dd>
                    </div>
                @endif

                {{-- Pays côtiers --}}
                @if ($area->coastalCountries->isNotEmpty())
                    <div class="flex items-baseline gap-2 min-w-0 sm:col-span-2">
                        <dt class="text-sm font-bold text-slate-700 uppercase tracking-wide shrink-0 w-28">Pays côtiers</dt>
                        <dd class="text-sm text-slate-800">
                            @foreach ($area->coastalCountries->sortBy('name') as $country)
                                <button
                                    @click="$dispatch('switch-to-pays', { countryId: {{ $country->id }} })"
                                    class="hover:underline cursor-pointer"
                                    title="Voir {{ $country->name }} dans la carte des pays"
                                >{{ $country->name }}</button>@if (! $loop->last), @endif
                            @endforeach
                        </dd>
                    </div>
                @endif

            </dl>
        </section>

    @endif

</div>
