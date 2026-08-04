<?php

use App\Models\Continent;
use App\Models\Country;
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

    // Form fields
    public string $name = '';

    public string $isoCode = '';

    public ?int $continentId = null;

    public string $latitude = '';

    public string $longitude = '';

    // Delete
    public ?int $confirmingDeleteId = null;

    public string $successMessage = '';

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
                ->orWhere('iso_code', 'like', "%{$this->search}%"))
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
        $country = Country::findOrFail($id);
        $this->name = $country->name;
        $this->isoCode = $country->iso_code;
        $this->continentId = $country->continent_id;
        $this->latitude = $country->latitude !== null ? (string) $country->latitude : '';
        $this->longitude = $country->longitude !== null ? (string) $country->longitude : '';
        $this->editingId = $id;
        $this->showForm = true;
        $this->successMessage = '';
    }

    public function save(): void
    {
        $this->isoCode = strtoupper(trim($this->isoCode));

        $this->validate([
            'name' => 'required|string|max:100',
            'isoCode' => 'required|string|size:3|alpha',
            'continentId' => 'required|exists:continents,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $isoExists = Country::where('iso_code', $this->isoCode)
            ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
            ->exists();

        if ($isoExists) {
            $this->addError('isoCode', 'Ce code ISO existe déjà.');

            return;
        }

        $data = [
            'name' => trim($this->name),
            'iso_code' => $this->isoCode,
            'continent_id' => $this->continentId,
            'slug' => Str::slug(trim($this->name)),
            'latitude' => $this->latitude !== '' ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== '' ? (float) $this->longitude : null,
        ];

        if ($this->editingId) {
            Country::findOrFail($this->editingId)->update($data);
            $this->successMessage = 'Pays mis à jour avec succès.';
        } else {
            Country::create($data);
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
        $this->continentId = null;
        $this->latitude = '';
        $this->longitude = '';
        $this->confirmingDeleteId = null;
        $this->resetValidation();
    }
};
?>

<div>
    @if ($showForm)
        {{-- Create / Edit form --}}
        <div class="mb-6">
            <button wire:click="cancelForm" class="text-sm text-slate-500 hover:text-slate-700 flex items-center gap-1">
                ← Retour à la liste
            </button>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 max-w-lg">
            <h2 class="text-xl font-bold text-slate-900 mb-6">
                {{ $editingId ? 'Modifier le pays' : 'Ajouter un pays' }}
            </h2>

            <form wire:submit="save" class="space-y-4">
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

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Code ISO (3 lettres)</label>
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

                <div class="flex gap-3 pt-2">
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
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $country->name }}</td>
                                <td class="px-4 py-3 font-mono text-slate-500 text-xs">{{ $country->iso_code }}</td>
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
