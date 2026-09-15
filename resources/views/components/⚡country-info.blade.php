<?php

use App\Models\Country;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?int $countryId = null;

    #[On('country-selected')]
    public function selectCountry(int $countryId): void
    {
        $this->countryId = $countryId;
    }

    #[On('continent-selected')]
    public function clearOnContinent(): void
    {
        $this->countryId = null;
    }

    #[On('map-reset')]
    public function clearOnReset(): void
    {
        $this->countryId = null;
    }

    #[Computed]
    public function info()
    {
        if (! $this->countryId) {
            return null;
        }

        return Country::with('info')->find($this->countryId)?->info;
    }
};
?>

<div aria-live="polite" aria-atomic="true" class="lg:shrink-0">
    @if ($this->info)
        @php
            $info = $this->info;
            $isSovereign = ! $info->entity_type || $info->entity_type === 'sovereign_state';
            $entityLabels = [
                'constituent_country'   => 'Nation constitutive',
                'autonomous_community'  => 'Communauté autonome',
                'autonomous_city'       => 'Ville autonome',
                'autonomous_region'     => 'Région autonome',
                'administrative_region' => 'Région administrative',
                'federal_subject'       => 'République fédérée',
                'geographic_entity'     => 'Entité géographique',
                'unrecognized_state'    => 'État non reconnu',
                'transnational_region'  => 'Région transnationale',
            ];
        @endphp
        <section
            aria-labelledby="info-heading"
            class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden"
        >
            <div class="px-5 py-3 border-b border-slate-100 flex items-center gap-2">
                <span class="text-emerald-500 font-bold text-lg leading-none" aria-hidden="true">•</span>
                <h3 id="info-heading" class="text-sm font-semibold text-slate-700">INFO</h3>
            </div>

            <dl class="px-5 py-4 grid grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-3">
                @unless ($isSovereign)
                    <div class="flex items-baseline gap-2 min-w-0">
                        <dt class="text-xs text-slate-400 uppercase tracking-wide shrink-0 w-24">Statut</dt>
                        <dd class="text-sm text-slate-800">{{ $entityLabels[$info->entity_type] ?? $info->entity_type }}</dd>
                    </div>
                    @if ($info->parent_country)
                        <div class="flex items-baseline gap-2 min-w-0">
                            <dt class="text-xs text-slate-400 uppercase tracking-wide shrink-0 w-24">Fait partie de</dt>
                            <dd class="text-sm text-slate-800">{{ $info->parent_country }}</dd>
                        </div>
                    @endif
                @endunless

                @if ($info->capital)
                    <div class="flex items-baseline gap-2 min-w-0">
                        <dt class="text-xs font-bold text-slate-900 uppercase tracking-wide shrink-0 w-24">Capitale</dt>
                        <dd class="text-sm font-medium text-slate-800 truncate">{{ $info->capital }}</dd>
                    </div>
                @endif

                @if ($info->population !== null)
                    <div class="flex items-baseline gap-2 min-w-0">
                        <dt class="text-xs font-bold text-slate-900 uppercase tracking-wide shrink-0 w-24">Population</dt>
                        <dd class="text-sm text-slate-800">
                            {{ $info->formatted_population }}
                            @if ($info->population_year)
                                <span class="text-xs text-slate-400">({{ $info->population_year }})</span>
                            @endif
                        </dd>
                    </div>
                @endif

                @if ($info->languages)
                    <div class="flex items-baseline gap-2 min-w-0">
                        <dt class="text-xs font-bold text-slate-900 uppercase tracking-wide shrink-0 w-24">Langue(s)</dt>
                        <dd class="text-sm text-slate-800 truncate">{{ implode(', ', array_column($info->languages, 'name')) }}</dd>
                    </div>
                @endif

                @if ($info->currency)
                    <div class="flex items-baseline gap-2 min-w-0">
                        <dt class="text-xs font-bold text-slate-900 uppercase tracking-wide shrink-0 w-24">Monnaie</dt>
                        <dd class="text-sm text-slate-800">{{ $info->currency_label }}</dd>
                    </div>
                @endif
            </dl>
        </section>
    @endif
</div>
