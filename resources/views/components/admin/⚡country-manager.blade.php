<?php

use App\Models\Continent;
use App\Models\Country;
use App\Models\CountryInfo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    // Filters
    public string $search = '';

    public ?int $filterContinentId = null;

    // Create/edit form state
    public bool $showForm = false;

    public ?int $editingId = null;

    // Identity fields
    public string $name = '';

    public string $isoCode = '';

    public string $iso2 = '';

    public ?int $continentId = null;

    public string $latitude = '';

    public string $longitude = '';

    // Info fields
    public string $entityType = '';

    public string $parentCountry = '';

    public string $capital = '';

    public string $languagesRaw = '';

    public string $population = '';

    public string $populationYear = '';

    public string $currency = '';

    public string $currencyCode = '';

    // Sign languages
    /** @var array<int, array{name: string, sigle: string, year_official: string}> */
    public array $signLanguages = [];

    // Delete
    public ?int $confirmingDeleteId = null;

    public string $successMessage = '';

    /** @var array<string, string> */
    public const ENTITY_TYPES = [
        'constituent_country' => 'Nation constitutive',
        'autonomous_community' => 'Communauté autonome',
        'autonomous_city' => 'Ville autonome',
        'autonomous_region' => 'Région autonome',
        'administrative_region' => 'Région administrative',
        'federal_subject' => 'République fédérée',
        'geographic_entity' => 'Entité géographique',
        'unrecognized_state' => 'État non reconnu',
        'transnational_region' => 'Région transnationale',
    ];

    #[Computed]
    public function continents(): Collection
    {
        return Continent::orderBy('name')->get();
    }

    #[Computed]
    public function countries(): LengthAwarePaginator
    {
        return Country::with(['continent', 'lsfbVideo', 'internationalVideo'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('iso3', 'like', "%{$this->search}%"))
            ->when($this->filterContinentId, fn ($q) => $q->where('continent_id', $this->filterContinentId))
            ->orderBy('name')
            ->paginate(20);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->successMessage = '';
    }

    public function updatedFilterContinentId(): void
    {
        $this->resetPage();
        $this->successMessage = '';
    }

    public function startCreate(): void
    {
        $this->clearForm();
        $this->editingId = null;
        $this->showForm = true;
        $this->successMessage = '';
    }

    public function startEdit(int $id): void
    {
        $country = Country::with(['info', 'signLanguages'])->findOrFail($id);

        $this->name = $country->name;
        $this->isoCode = $country->iso3 ?? '';
        $this->iso2 = $country->iso2 ?? '';
        $this->continentId = $country->continent_id;
        $this->latitude = $country->latitude !== null ? (string) $country->latitude : '';
        $this->longitude = $country->longitude !== null ? (string) $country->longitude : '';

        $info = $country->info;
        $this->entityType = $info?->entity_type ?? '';
        $this->parentCountry = $info?->parent_country ?? '';
        $this->capital = $info?->capital ?? '';
        $this->languagesRaw = $info?->languages
            ? implode(', ', array_map(fn ($l) => is_array($l) ? ($l['name'] ?? '') : $l, $info->languages))
            : '';
        $this->population = $info?->population !== null ? (string) $info->population : '';
        $this->populationYear = $info?->population_year !== null ? (string) $info->population_year : '';
        $this->currency = $info?->currency ?? '';
        $this->currencyCode = $info?->currency_code ?? '';

        $this->signLanguages = $country->signLanguages
            ->sortBy('year_official')
            ->values()
            ->map(fn ($sl) => [
                'name' => $sl->name,
                'sigle' => $sl->sigle ?? '',
                'year_official' => $sl->year_official !== null ? (string) $sl->year_official : '',
            ])
            ->toArray();

        $this->editingId = $id;
        $this->showForm = true;
        $this->successMessage = '';
    }

    public function addSignLanguage(): void
    {
        $this->signLanguages[] = ['name' => '', 'sigle' => '', 'year_official' => ''];
    }

    public function removeSignLanguage(int $index): void
    {
        array_splice($this->signLanguages, $index, 1);
        $this->signLanguages = array_values($this->signLanguages);
    }

    public function save(): void
    {
        $this->isoCode = strtoupper(trim($this->isoCode));
        $this->iso2 = strtoupper(trim($this->iso2));

        $this->validate([
            'name' => 'required|string|max:100',
            'isoCode' => 'nullable|string|size:3|alpha',
            'iso2' => 'nullable|string|size:2|alpha',
            'continentId' => 'required|exists:continents,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'entityType' => 'nullable|in:'.implode(',', array_keys(self::ENTITY_TYPES)),
            'parentCountry' => 'nullable|string|max:100',
            'capital' => 'nullable|string|max:100',
            'languagesRaw' => 'nullable|string|max:255',
            'population' => 'nullable|integer|min:0',
            'populationYear' => 'nullable|integer|min:1900|max:2100',
            'currency' => 'nullable|string|max:100',
            'currencyCode' => 'nullable|string|max:10',
            'signLanguages.*.name' => 'required|string|max:255',
            'signLanguages.*.sigle' => 'nullable|string|max:20',
            'signLanguages.*.year_official' => 'nullable|integer|min:1500|max:2100',
        ]);

        if ($this->isoCode) {
            $isoExists = Country::where('iso3', $this->isoCode)
                ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
                ->exists();

            if ($isoExists) {
                $this->addError('isoCode', 'Ce code ISO 3 existe déjà.');

                return;
            }
        }

        if ($this->iso2) {
            $iso2Exists = Country::where('iso2', $this->iso2)
                ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
                ->exists();

            if ($iso2Exists) {
                $this->addError('iso2', 'Ce code ISO 2 existe déjà.');

                return;
            }
        }

        $countryData = [
            'name' => trim($this->name),
            'iso3' => $this->isoCode ?: null,
            'iso2' => $this->iso2 ?: null,
            'continent_id' => $this->continentId,
            'slug' => Str::slug(trim($this->name)),
            'latitude' => $this->latitude !== '' ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== '' ? (float) $this->longitude : null,
        ];

        $languageNames = $this->languagesRaw !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $this->languagesRaw))))
            : [];
        $languages = $languageNames ? array_map(fn ($l) => ['name' => $l], $languageNames) : null;

        $infoData = [
            'entity_type' => $this->entityType ?: null,
            'parent_country' => $this->parentCountry !== '' ? trim($this->parentCountry) : null,
            'capital' => $this->capital !== '' ? trim($this->capital) : null,
            'languages' => $languages,
            'population' => $this->population !== '' ? (int) $this->population : null,
            'population_year' => $this->populationYear !== '' ? (int) $this->populationYear : null,
            'currency' => $this->currency !== '' ? trim($this->currency) : null,
            'currency_code' => $this->currencyCode !== '' ? strtoupper(trim($this->currencyCode)) : null,
        ];

        $hasInfo = collect($infoData)->filter()->isNotEmpty();

        if ($this->editingId) {
            $country = Country::findOrFail($this->editingId);
            $country->update($countryData);

            if ($hasInfo) {
                $country->info()->updateOrCreate([], $infoData);
            } else {
                $country->info()->delete();
            }

            $country->signLanguages()->delete();

            foreach ($this->signLanguages as $sl) {
                $slName = trim($sl['name'] ?? '');
                if ($slName !== '') {
                    $country->signLanguages()->create([
                        'name' => $slName,
                        'sigle' => trim($sl['sigle'] ?? '') ?: null,
                        'year_official' => ($sl['year_official'] ?? '') !== '' ? (int) $sl['year_official'] : null,
                    ]);
                }
            }

            $this->successMessage = 'Pays mis à jour avec succès.';
        } else {
            $country = Country::create($countryData);

            if ($hasInfo) {
                $country->info()->create($infoData);
            }

            foreach ($this->signLanguages as $sl) {
                $slName = trim($sl['name'] ?? '');
                if ($slName !== '') {
                    $country->signLanguages()->create([
                        'name' => $slName,
                        'sigle' => trim($sl['sigle'] ?? '') ?: null,
                        'year_official' => ($sl['year_official'] ?? '') !== '' ? (int) $sl['year_official'] : null,
                    ]);
                }
            }

            $this->successMessage = 'Pays créé avec succès.';
        }

        $this->clearForm();
        $this->showForm = false;
        $this->editingId = null;
        unset($this->countries);
    }

    public function cancelForm(): void
    {
        $this->clearForm();
        $this->showForm = false;
        $this->editingId = null;
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function delete(): void
    {
        Country::findOrFail($this->confirmingDeleteId)->delete();
        $this->confirmingDeleteId = null;
        $this->successMessage = 'Pays supprimé.';
        unset($this->countries);
    }

    private function clearForm(): void
    {
        $this->name = '';
        $this->isoCode = '';
        $this->iso2 = '';
        $this->continentId = null;
        $this->latitude = '';
        $this->longitude = '';
        $this->entityType = '';
        $this->parentCountry = '';
        $this->capital = '';
        $this->languagesRaw = '';
        $this->population = '';
        $this->populationYear = '';
        $this->currency = '';
        $this->currencyCode = '';
        $this->signLanguages = [];
        $this->confirmingDeleteId = null;
        $this->resetValidation();
    }
};
?>

<div>
    @if ($showForm)
        <div class="mb-6">
            <button wire:click="cancelForm" class="text-sm text-slate-500 hover:text-slate-700 flex items-center gap-1">
                ← Retour à la liste
            </button>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 max-w-2xl">
            <h2 class="text-xl font-bold text-slate-900 mb-6">
                {{ $editingId ? 'Modifier le pays' : 'Ajouter un pays' }}
            </h2>

            <form wire:submit="save" class="space-y-6">

                {{-- Identité --}}
                <div class="space-y-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Identité</p>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nom du pays</label>
                        <input
                            type="text"
                            wire:model="name"
                            class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Belgique"
                            autofocus
                        >
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Code ISO 3</label>
                            <input
                                type="text"
                                wire:model="isoCode"
                                maxlength="3"
                                class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="BEL"
                            >
                            @error('isoCode') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                Code ISO 2
                                <span class="ml-1 text-slate-400 font-normal">(drapeau)</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input
                                    type="text"
                                    wire:model.live="iso2"
                                    maxlength="2"
                                    class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="BE"
                                >
                                @if (strlen($iso2) === 2)
                                    <img
                                        src="https://flagcdn.com/32x24/{{ strtolower($iso2) }}.png"
                                        alt=""
                                        aria-hidden="true"
                                        class="h-6 w-auto rounded-sm shrink-0 shadow-sm"
                                    >
                                @endif
                            </div>
                            @error('iso2') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Continent</label>
                        <select
                            wire:model="continentId"
                            class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="">— Sélectionner —</option>
                            @foreach($this->continents as $continent)
                                <option value="{{ $continent->id }}">{{ $continent->name }}</option>
                            @endforeach
                        </select>
                        @error('continentId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Latitude</label>
                            <input
                                type="number"
                                wire:model="latitude"
                                step="0.0001"
                                min="-90"
                                max="90"
                                class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="50.8503"
                            >
                            @error('latitude') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Longitude</label>
                            <input
                                type="number"
                                wire:model="longitude"
                                step="0.0001"
                                min="-180"
                                max="180"
                                class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="4.3517"
                            >
                            @error('longitude') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Informations --}}
                <div class="pt-4 border-t border-slate-100 space-y-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Informations</p>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Statut</label>
                            <select
                                wire:model.live="entityType"
                                class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                <option value="">État souverain</option>
                                @foreach (self::ENTITY_TYPES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('entityType') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        @if ($entityType)
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Fait partie de</label>
                                <input
                                    type="text"
                                    wire:model="parentCountry"
                                    class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Espagne"
                                >
                                @error('parentCountry') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Capitale</label>
                        <input
                            type="text"
                            wire:model="capital"
                            class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Bruxelles"
                        >
                        @error('capital') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Langue(s)
                            <span class="ml-1 text-slate-400 font-normal">(séparées par des virgules)</span>
                        </label>
                        <input
                            type="text"
                            wire:model="languagesRaw"
                            class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Français, Néerlandais, Allemand"
                        >
                        @error('languagesRaw') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Population</label>
                            <input
                                type="number"
                                wire:model="population"
                                min="0"
                                class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="11600000"
                            >
                            @error('population') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Année</label>
                            <input
                                type="number"
                                wire:model="populationYear"
                                min="1900"
                                max="2100"
                                class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="2024"
                            >
                            @error('populationYear') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Monnaie</label>
                            <input
                                type="text"
                                wire:model="currency"
                                class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Euro"
                            >
                            @error('currency') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Code</label>
                            <input
                                type="text"
                                wire:model="currencyCode"
                                maxlength="10"
                                class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="EUR"
                            >
                            @error('currencyCode') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Langues des signes --}}
                <div class="pt-4 border-t border-slate-100 space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Langues des signes</p>
                        <button
                            type="button"
                            wire:click="addSignLanguage"
                            class="text-xs font-semibold text-indigo-600 hover:text-indigo-800"
                        >
                            + Ajouter
                        </button>
                    </div>

                    @if (count($signLanguages) === 0)
                        <p class="text-xs text-slate-400 italic">Aucune langue des signes.</p>
                    @endif

                    @foreach ($signLanguages as $i => $sl)
                        <div class="grid grid-cols-12 gap-2 items-start bg-slate-50 rounded-lg p-3">
                            <div class="col-span-5">
                                <input
                                    type="text"
                                    wire:model="signLanguages.{{ $i }}.name"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Langue des signes belge"
                                >
                                @error("signLanguages.{$i}.name") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="col-span-3">
                                <input
                                    type="text"
                                    wire:model="signLanguages.{{ $i }}.sigle"
                                    maxlength="20"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="LSFB"
                                >
                                @error("signLanguages.{$i}.sigle") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="col-span-3">
                                <input
                                    type="number"
                                    wire:model="signLanguages.{{ $i }}.year_official"
                                    min="1500"
                                    max="2100"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="2003"
                                >
                                @error("signLanguages.{$i}.year_official") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="col-span-1 flex items-center justify-center pt-2">
                                <button
                                    type="button"
                                    wire:click="removeSignLanguage({{ $i }})"
                                    class="text-slate-400 hover:text-red-500 transition-colors"
                                    title="Supprimer"
                                >
                                    ✕
                                </button>
                            </div>
                        </div>
                        @if ($loop->first && count($signLanguages) > 0)
                            <div class="grid grid-cols-12 gap-2 px-3">
                                <p class="col-span-5 text-xs text-slate-400">Nom</p>
                                <p class="col-span-3 text-xs text-slate-400">Sigle</p>
                                <p class="col-span-3 text-xs text-slate-400">Année officielle</p>
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- Actions --}}
                <div class="flex gap-3 pt-2 border-t border-slate-100">
                    <button
                        type="submit"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-colors"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>{{ $editingId ? 'Enregistrer' : 'Créer le pays' }}</span>
                        <span wire:loading class="opacity-70">Enregistrement…</span>
                    </button>
                    <button
                        type="button"
                        wire:click="cancelForm"
                        class="px-4 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-600 hover:bg-slate-50 transition-colors"
                    >
                        Annuler
                    </button>
                </div>

                @if ($editingId)
                    <div class="pt-2">
                        <a
                            href="{{ route('admin.videos', $editingId) }}"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-violet-600 hover:text-violet-800"
                        >
                            🎬 Gérer les vidéos →
                        </a>
                    </div>
                @endif

            </form>
        </div>

    @else
        {{-- Country list --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <h2 class="text-2xl font-bold text-slate-900">Pays <span class="text-slate-400 font-normal text-lg">({{ $this->countries->total() }})</span></h2>
            <button
                wire:click="startCreate"
                class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition-colors"
            >
                + Ajouter un pays
            </button>
        </div>

        @if ($successMessage)
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm mb-5">
                {{ $successMessage }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="flex flex-col sm:flex-row gap-3 mb-5">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher par nom ou code ISO…"
                class="flex-1 rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
            <select
                wire:model.live="filterContinentId"
                class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
                <option value="">Tous les continents</option>
                @foreach($this->continents as $continent)
                    <option value="{{ $continent->id }}">{{ $continent->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-slate-600">Nom</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-600">ISO</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-600 hidden sm:table-cell">Continent</th>
                            <th class="text-center px-3 py-3 font-semibold text-slate-600">LSFB</th>
                            <th class="text-center px-3 py-3 font-semibold text-slate-600">Intern.</th>
                            <th class="text-right px-4 py-3 font-semibold text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($this->countries as $country)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-slate-900">
                                    <span class="flex items-center gap-2">
                                        @if ($country->flag_path)
                                            <img src="{{ asset($country->flag_path) }}" alt="" aria-hidden="true" class="h-4 w-auto rounded-sm">
                                        @elseif ($country->iso2)
                                            <img src="https://flagcdn.com/20x15/{{ strtolower($country->iso2) }}.png" alt="" aria-hidden="true" class="h-4 w-auto rounded-sm">
                                        @endif
                                        {{ $country->name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-500 text-xs">{{ $country->iso3 }}</td>
                                <td class="px-4 py-3 text-slate-600 hidden sm:table-cell">{{ $country->continent->name }}</td>
                                <td class="px-3 py-3 text-center">
                                    @if ($country->lsfbVideo)
                                        <span class="text-green-500 font-bold" title="Vidéo disponible">✓</span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if ($country->internationalVideo)
                                        <span class="text-green-500 font-bold" title="Vidéo disponible">✓</span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if ($confirmingDeleteId === $country->id)
                                        <span class="text-xs text-slate-600 mr-2">Confirmer ?</span>
                                        <button wire:click="delete" class="text-xs text-red-600 font-semibold mr-2 hover:text-red-700">
                                            Oui, supprimer
                                        </button>
                                        <button wire:click="cancelDelete" class="text-xs text-slate-400 hover:text-slate-600">
                                            Annuler
                                        </button>
                                    @else
                                        <button
                                            wire:click="startEdit({{ $country->id }})"
                                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium mr-3"
                                        >
                                            Éditer
                                        </button>
                                        <a
                                            href="{{ route('admin.videos', $country) }}"
                                            class="text-xs text-violet-600 hover:text-violet-800 font-medium mr-3"
                                        >
                                            Vidéos
                                        </a>
                                        <button
                                            wire:click="confirmDelete({{ $country->id }})"
                                            class="text-xs text-red-400 hover:text-red-600"
                                        >
                                            Supprimer
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">
                                    Aucun pays trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($this->countries->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">
                    {{ $this->countries->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
