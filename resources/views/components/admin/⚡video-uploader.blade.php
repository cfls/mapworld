<?php

use App\Enums\SignVideoType;
use App\Models\Country;
use App\Models\SignVideo;
use App\Services\CloudinaryService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public Country $country;

    public bool $cloudinaryConfigured = false;

    public string $successMessage = '';

    public string $errorMessage = '';

    #[Validate(['lsfbFile' => 'nullable|mimetypes:video/mp4,video/webm,video/ogg|max:204800'])]
    public $lsfbFile = null;

    #[Validate(['internationalFile' => 'nullable|mimetypes:video/mp4,video/webm,video/ogg|max:204800'])]
    public $internationalFile = null;

    public function mount(Country $country): void
    {
        $this->country = $country;
        $this->cloudinaryConfigured = app(CloudinaryService::class)->isConfigured();
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, SignVideo> */
    #[Computed]
    public function lsfbVideos(): \Illuminate\Database\Eloquent\Collection
    {
        return SignVideo::where('country_id', $this->country->id)
            ->where('type', SignVideoType::Lsfb->value)
            ->orderBy('cloudinary_public_id')
            ->get();
    }

    #[Computed]
    public function internationalVideo(): ?SignVideo
    {
        return SignVideo::where('country_id', $this->country->id)
            ->where('type', SignVideoType::International->value)
            ->first();
    }

    public function uploadLsfb(): void
    {
        $this->validateOnly('lsfbFile');
        $this->clearMessages();

        if (! $this->lsfbFile) {
            return;
        }

        $this->uploadVideo(SignVideoType::Lsfb, $this->lsfbFile);
        $this->lsfbFile = null;
        unset($this->lsfbVideos);
    }

    public function uploadInternational(): void
    {
        $this->validateOnly('internationalFile');
        $this->clearMessages();

        if (! $this->internationalFile) {
            return;
        }

        $this->uploadVideo(SignVideoType::International, $this->internationalFile);
        $this->internationalFile = null;
        unset($this->internationalVideo);
    }

    public function deleteVideo(int $videoId): void
    {
        $this->clearMessages();
        $video = SignVideo::findOrFail($videoId);
        $type = $video->type;

        try {
            app(CloudinaryService::class)->deleteVideo($video->cloudinary_public_id);
        } catch (\Throwable) {
            // Proceed with DB deletion even if Cloudinary call fails
        }

        $video->delete();

        if ($type === SignVideoType::Lsfb) {
            unset($this->lsfbVideos);
        } else {
            unset($this->internationalVideo);
        }

        $this->successMessage = 'Vidéo supprimée.';
    }

    private function uploadVideo(SignVideoType $type, mixed $file): void
    {
        try {
            $result = app(CloudinaryService::class)->uploadVideo($file->getPathname());

            if ($type === SignVideoType::International) {
                SignVideo::updateOrCreate(
                    ['country_id' => $this->country->id, 'type' => $type->value],
                    [
                        'cloudinary_public_id' => $result['public_id'],
                        'cloudinary_url' => $result['secure_url'],
                        'thumbnail_url' => $result['thumbnail_url'],
                        'duration_seconds' => $result['duration'] ? (int) $result['duration'] : null,
                    ]
                );
            } else {
                SignVideo::create([
                    'country_id' => $this->country->id,
                    'type' => $type->value,
                    'cloudinary_public_id' => $result['public_id'],
                    'cloudinary_url' => $result['secure_url'],
                    'thumbnail_url' => $result['thumbnail_url'],
                    'duration_seconds' => $result['duration'] ? (int) $result['duration'] : null,
                ]);
            }

            $this->successMessage = 'Vidéo téléchargée avec succès.';
        } catch (\Throwable $e) {
            $this->errorMessage = 'Erreur lors du téléchargement : '.$e->getMessage();
        }
    }

    private function clearMessages(): void
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }
};
?>

<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm mb-6">
        <a href="{{ route('admin.countries') }}" class="text-slate-500 hover:text-indigo-700">Pays</a>
        <span class="text-slate-300">/</span>
        <span class="text-slate-700 font-medium">{{ $this->country->name }}</span>
        <span class="text-slate-300">/</span>
        <span class="text-slate-900 font-semibold">Vidéos</span>
    </div>

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-900">
            Vidéos — <span class="text-indigo-700">{{ $this->country->name }}</span>
            <span class="text-slate-400 font-mono text-base ml-2">{{ $this->country->iso3 }}</span>
        </h2>
    </div>

    {{-- Messages --}}
    @if ($successMessage)
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm mb-5">
            {{ $successMessage }}
        </div>
    @endif

    @if ($errorMessage)
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-5">
            {{ $errorMessage }}
        </div>
    @endif

    @if (! $cloudinaryConfigured)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-6">
            <p class="text-sm font-semibold text-amber-800 mb-1">Cloudinary non configuré</p>
            <p class="text-sm text-amber-700">
                Ajoutez ces variables dans votre <code class="bg-amber-100 px-1 rounded">.env</code> pour activer l'upload de vidéos :
            </p>
            <pre class="mt-2 text-xs text-amber-800 bg-amber-100 rounded-lg p-3 font-mono">CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret</pre>
        </div>
    @endif

    {{-- Video cards --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- LSFB --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center gap-2 mb-4">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 inline-block"></span>
                <h3 class="font-semibold text-slate-900">LSFB</h3>
                @if ($this->lsfbVideos->count() > 0)
                    <span class="text-xs text-slate-400 font-normal">({{ $this->lsfbVideos->count() }} vidéo{{ $this->lsfbVideos->count() > 1 ? 's' : '' }})</span>
                @endif
            </div>

            @if ($this->lsfbVideos->isNotEmpty())
                <div class="space-y-4 mb-4">
                    @foreach ($this->lsfbVideos as $index => $lsfbVideo)
                        <div>
                            @if ($this->lsfbVideos->count() > 1)
                                <p class="text-xs text-slate-500 font-medium mb-1">Vidéo {{ $index + 1 }}</p>
                            @endif
                            <video
                                class="w-full aspect-video rounded-lg bg-black mb-2"
                                controls
                                preload="metadata"
                                @if ($lsfbVideo->thumbnail_url)
                                    poster="{{ $lsfbVideo->thumbnail_url }}"
                                @endif
                            >
                                <source src="{{ $lsfbVideo->cloudinary_url }}" type="video/mp4">
                            </video>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-400 font-mono truncate max-w-[200px]" title="{{ $lsfbVideo->cloudinary_public_id }}">
                                    {{ $lsfbVideo->cloudinary_public_id }}
                                </span>
                                <button
                                    wire:click="deleteVideo({{ $lsfbVideo->id }})"
                                    wire:confirm="Supprimer cette vidéo LSFB ?"
                                    class="text-xs text-red-500 hover:text-red-700 font-medium ml-2 shrink-0"
                                >
                                    Supprimer
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="w-full aspect-video rounded-lg bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center mb-4">
                    <p class="text-xs text-slate-400">Pas de vidéo LSFB</p>
                </div>
            @endif

            @if ($cloudinaryConfigured)
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">
                            Ajouter une vidéo LSFB
                        </label>
                        <input
                            type="file"
                            wire:model="lsfbFile"
                            accept="video/mp4,video/webm,video/ogg"
                            class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
                        >
                        @error('lsfbFile') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div wire:loading wire:target="lsfbFile" class="text-xs text-slate-500">
                        Préparation du fichier…
                    </div>

                    @if ($lsfbFile)
                        <button
                            wire:click="uploadLsfb"
                            wire:loading.attr="disabled"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold py-2 px-4 rounded-lg transition-colors disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="uploadLsfb">Télécharger sur Cloudinary</span>
                            <span wire:loading wire:target="uploadLsfb">Upload en cours…</span>
                        </button>
                    @endif
                </div>
            @endif
        </div>

        {{-- Signes Internationaux --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center gap-2 mb-4">
                <span class="w-2.5 h-2.5 rounded-full bg-violet-500 inline-block"></span>
                <h3 class="font-semibold text-slate-900">Signes Internationaux</h3>
            </div>

            @if ($this->internationalVideo)
                <video
                    class="w-full aspect-video rounded-lg bg-black mb-3"
                    controls
                    preload="metadata"
                    @if ($this->internationalVideo->thumbnail_url)
                        poster="{{ $this->internationalVideo->thumbnail_url }}"
                    @endif
                >
                    <source src="{{ $this->internationalVideo->cloudinary_url }}" type="video/mp4">
                </video>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs text-slate-400 font-mono truncate max-w-[200px]" title="{{ $this->internationalVideo->cloudinary_public_id }}">
                        {{ $this->internationalVideo->cloudinary_public_id }}
                    </span>
                    <button
                        wire:click="deleteVideo({{ $this->internationalVideo->id }})"
                        wire:confirm="Supprimer cette vidéo en Signes Internationaux ?"
                        class="text-xs text-red-500 hover:text-red-700 font-medium ml-2 shrink-0"
                    >
                        Supprimer
                    </button>
                </div>
            @else
                <div class="w-full aspect-video rounded-lg bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center mb-4">
                    <p class="text-xs text-slate-400">Pas de vidéo en Signes Internationaux</p>
                </div>
            @endif

            @if ($cloudinaryConfigured)
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">
                            {{ $this->internationalVideo ? 'Remplacer la vidéo' : 'Choisir une vidéo' }}
                        </label>
                        <input
                            type="file"
                            wire:model="internationalFile"
                            accept="video/mp4,video/webm,video/ogg"
                            class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 cursor-pointer"
                        >
                        @error('internationalFile') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div wire:loading wire:target="internationalFile" class="text-xs text-slate-500">
                        Préparation du fichier…
                    </div>

                    @if ($internationalFile)
                        <button
                            wire:click="uploadInternational"
                            wire:loading.attr="disabled"
                            class="w-full bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold py-2 px-4 rounded-lg transition-colors disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="uploadInternational">Télécharger sur Cloudinary</span>
                            <span wire:loading wire:target="uploadInternational">Upload en cours…</span>
                        </button>
                    @endif
                </div>
            @endif
        </div>

    </div>
</div>
