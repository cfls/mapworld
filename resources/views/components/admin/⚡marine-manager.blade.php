<?php

use App\Models\MarineArea;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $type = 'sea';

    public string $oceanGroup = '';

    public ?int $confirmingDeleteId = null;

    public string $successMessage = '';

    /** @var array<string, string> */
    public const TYPE_LABELS = [
        'ocean' => 'Océan',
        'sea' => 'Mer',
        'gulf' => 'Golfe',
        'bay' => 'Baie',
        'other' => 'Autre',
    ];

    /** @var array<string, string> */
    public const GROUP_LABELS = [
        'pacifique' => 'Pacifique',
        'atlantique' => 'Atlantique',
        'indien' => 'Indien',
        'arctique' => 'Arctique',
        'austral' => 'Austral',
    ];

    #[Computed]
    public function areas(): LengthAwarePaginator
    {
        return MarineArea::withCount('signVideos')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(20);
    }

    public function updatedSearch(): void
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
        $area = MarineArea::findOrFail($id);
        $this->name = $area->name;
        $this->type = $area->type;
        $this->oceanGroup = $area->ocean_group ?? '';
        $this->editingId = $id;
        $this->showForm = true;
        $this->successMessage = '';
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:150',
            'type' => 'required|in:ocean,sea,gulf,bay,other',
            'oceanGroup' => 'nullable|in:pacifique,atlantique,indien,arctique,austral',
        ]);

        $data = [
            'name' => trim($this->name),
            'slug' => Str::slug(trim($this->name)),
            'type' => $this->type,
            'ocean_group' => $this->oceanGroup ?: null,
        ];

        if ($this->editingId) {
            MarineArea::findOrFail($this->editingId)->update($data);
            $this->successMessage = 'Aire marine mise à jour.';
        } else {
            MarineArea::create($data);
            $this->successMessage = 'Aire marine créée.';
        }

        $this->clearForm();
        $this->showForm = false;
        $this->editingId = null;
        unset($this->areas);
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
        MarineArea::findOrFail($this->confirmingDeleteId)->delete();
        $this->confirmingDeleteId = null;
        $this->successMessage = 'Aire marine supprimée.';
        unset($this->areas);
    }

    private function clearForm(): void
    {
        $this->name = '';
        $this->type = 'sea';
        $this->oceanGroup = '';
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

        <div class="bg-white rounded-xl border border-slate-200 p-6 max-w-lg">
            <h2 class="text-xl font-bold text-slate-900 mb-6">
                {{ $editingId ? 'Modifier l\'aire marine' : 'Ajouter une aire marine' }}
            </h2>

            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nom</label>
                    <input
                        type="text"
                        wire:model="name"
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"
                        placeholder="Mer Méditerranée"
                        autofocus
                    >
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Type</label>
                        <select
                            wire:model="type"
                            class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"
                        >
                            @foreach (self::TYPE_LABELS as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Groupe</label>
                        <select
                            wire:model="oceanGroup"
                            class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"
                        >
                            <option value="">— Aucun —</option>
                            @foreach (self::GROUP_LABELS as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('oceanGroup') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button
                        type="submit"
                        class="flex-1 bg-sky-600 hover:bg-sky-700 text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-colors"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>{{ $editingId ? 'Enregistrer' : 'Créer' }}</span>
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

            @if ($editingId)
                <div class="mt-5 pt-5 border-t border-slate-100">
                    <p class="text-xs text-slate-500 mb-2">Gérer les vidéos de signe :</p>
                    <a
                        href="{{ route('admin.marine-area-videos', $editingId) }}"
                        class="inline-flex items-center gap-1.5 text-sm font-semibold text-sky-600 hover:text-sky-800"
                    >
                        🎬 Gérer les vidéos →
                    </a>
                </div>
            @endif
        </div>

    @else
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <h2 class="text-2xl font-bold text-slate-900">
                Mers & Océans
                <span class="text-slate-400 font-normal text-lg">({{ $this->areas->total() }})</span>
            </h2>
            <button
                wire:click="startCreate"
                class="inline-flex items-center gap-1.5 bg-sky-600 hover:bg-sky-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition-colors"
            >
                + Ajouter
            </button>
        </div>

        @if ($successMessage)
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm mb-5">
                {{ $successMessage }}
            </div>
        @endif

        <div class="mb-5">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher par nom…"
                class="w-full sm:max-w-xs rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"
            >
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-slate-600">Nom</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-600">Type</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-600 hidden sm:table-cell">Groupe</th>
                            <th class="text-center px-3 py-3 font-semibold text-slate-600">Vidéos</th>
                            <th class="text-right px-4 py-3 font-semibold text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($this->areas as $area)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $area->name }}</td>
                                <td class="px-4 py-3 text-slate-500">
                                    {{ self::TYPE_LABELS[$area->type] ?? $area->type }}
                                </td>
                                <td class="px-4 py-3 text-slate-500 hidden sm:table-cell">
                                    {{ self::GROUP_LABELS[$area->ocean_group] ?? '—' }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if ($area->sign_videos_count > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            {{ $area->sign_videos_count }}
                                        </span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if ($confirmingDeleteId === $area->id)
                                        <span class="text-xs text-slate-600 mr-2">Confirmer ?</span>
                                        <button wire:click="delete" class="text-xs text-red-600 font-semibold mr-2 hover:text-red-700">
                                            Oui, supprimer
                                        </button>
                                        <button wire:click="cancelDelete" class="text-xs text-slate-400 hover:text-slate-600">
                                            Annuler
                                        </button>
                                    @else
                                        <button
                                            wire:click="startEdit({{ $area->id }})"
                                            class="text-xs text-sky-600 hover:text-sky-800 font-medium mr-3"
                                        >
                                            Éditer
                                        </button>
                                        <a
                                            href="{{ route('admin.marine-area-videos', $area) }}"
                                            class="text-xs text-violet-600 hover:text-violet-800 font-medium mr-3"
                                        >
                                            Vidéos
                                        </a>
                                        <button
                                            wire:click="confirmDelete({{ $area->id }})"
                                            class="text-xs text-red-400 hover:text-red-600"
                                        >
                                            Supprimer
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-400">
                                    Aucune aire marine trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($this->areas->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">
                    {{ $this->areas->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
