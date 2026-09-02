<?php

namespace App\Console\Commands;

use App\Enums\SignVideoType;
use App\Models\Country;
use App\Models\SignVideo;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

#[Signature('cloudinary:import-videos
    {--root=Pays du monde : Root Cloudinary folder}
    {--dry-run : Preview what would be imported without saving}
    {--force : Overwrite existing sign_videos records}')]
#[Description('Import sign language videos from Cloudinary into sign_videos table')]
class ImportCloudinaryVideos extends Command
{
    private string $cloudName;

    private string $apiKey;

    private string $apiSecret;

    public function handle(): int
    {
        $this->cloudName = config('services.cloudinary.cloud_name');
        $this->apiKey = config('services.cloudinary.api_key');
        $this->apiSecret = config('services.cloudinary.api_secret');

        if (! $this->cloudName || ! $this->apiKey || ! $this->apiSecret) {
            $this->error('Cloudinary credentials not configured in config/services.php.');

            return self::FAILURE;
        }

        $root = $this->option('root');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($dryRun) {
            $this->warn('[DRY RUN] No changes will be saved.');
        }

        if ($force && ! $dryRun) {
            SignVideo::truncate();
            $this->warn('Table sign_videos vidée (--force).');
        }

        $subfolders = $this->listSubfolders($root);

        if ($subfolders->isEmpty()) {
            $this->warn("No subfolders found in «{$root}». Aborting.");

            return self::FAILURE;
        }

        $this->info('Continents trouvés : '.$subfolders->implode(', '));

        $resources = $this->fetchVideosFromFolders($subfolders)
            ->sortBy('public_id')
            ->values();

        $this->info('Total vidéos trouvées : '.$resources->count());
        $this->newLine();

        $imported = 0;
        $skipped = 0;
        $notFound = [];

        foreach ($resources as $resource) {
            $publicId = $resource['public_id'];

            [$rawName, $type] = $this->parsePublicId($publicId);

            if (! $type) {
                $this->warn("  SKIP (format inconnu) : {$publicId}");
                $skipped++;

                continue;
            }

            if (in_array($rawName, self::REGIONAL_ENTRIES, strict: true)) {
                $this->line("  SKIP (entrée régionale) : {$publicId}");
                $skipped++;

                continue;
            }

            $country = $this->findCountry($rawName);

            if (! $country) {
                $notFound[] = "{$rawName} ({$publicId})";
                $skipped++;

                continue;
            }

            $label = $type === SignVideoType::International ? 'INT' : 'LSF';
            $this->line("  [{$label}] «{$publicId}» → {$country->name} (id={$country->id})");

            if (! $dryRun) {
                SignVideo::updateOrCreate(
                    ['cloudinary_public_id' => $publicId],
                    [
                        'signable_type' => Country::class,
                        'signable_id' => $country->id,
                        'type' => $type,
                        'cloudinary_url' => $resource['secure_url'],
                        'thumbnail_url' => $this->thumbnailUrl($publicId),
                        'duration_seconds' => isset($resource['duration'])
                            ? (int) round($resource['duration'])
                            : null,
                    ]
                );
            }

            $imported++;
        }

        $this->newLine();

        if ($notFound) {
            $this->warn(count($notFound).' entrée(s) non trouvée(s) dans la base de données :');

            foreach (array_unique($notFound) as $entry) {
                $this->warn("  - {$entry}");
            }

            $this->newLine();
        }

        $action = $dryRun ? 'À importer' : 'Importés';
        $this->info("{$action} : {$imported}  |  Ignorés : {$skipped}");

        return self::SUCCESS;
    }

    /**
     * Parse a Cloudinary public_id into [countryName, SignVideoType|null].
     *
     * Expected format: "{Name_With_Underscores}_{Int|B}_{6charSuffix}"
     * e.g. "Trinité_Et_Tobago_Int_karphq" → ["Trinité Et Tobago", International]
     *      "Panama_1_B_u8zuwx"           → ["Panama 1", Lsfb]
     *
     * @return array{0: string|null, 1: SignVideoType|null}
     */
    private function parsePublicId(string $publicId): array
    {
        if (! preg_match('/^(.+)_(Int|B)_[a-z0-9]+$/u', $publicId, $m)) {
            return [null, null];
        }

        $rawName = str_replace('_', ' ', $m[1]);
        $type = $m[2] === 'Int' ? SignVideoType::International : SignVideoType::Lsfb;

        return [$rawName, $type];
    }

    /**
     * Regional entries to skip — these are sign-language videos for a whole
     * region, not a specific country, so they have no row in countries.
     *
     * @var list<string>
     */
    private const REGIONAL_ENTRIES = [
        'Amérique',
        'Amérique Du Nord',
        'Amérique Centrale',
        'Amérique Du Sud',
    ];

    /**
     * Known name mismatches between Cloudinary filenames and DB country names.
     * Key = Cloudinary name (spaces), Value = DB name.
     *
     * @var array<string, string>
     */
    private const NAME_ALIASES = [
        'Puerto Rico' => 'Porto Rico',
        'Trinité Et Tobago' => 'Trinité-et-Tobago',
        'Sainte Lucie' => 'Sainte-Lucie',
    ];

    /**
     * Find a country by name with multiple fallbacks:
     *  1. Known alias (NAME_ALIASES)
     *  2. Exact match
     *  3. Case-insensitive match
     *  4. Spaces → hyphens (e.g., "Sainte Lucie" → "Sainte-Lucie")
     *  5. Strip trailing number (e.g., "Panama 1" → "Panama")
     *  6. Strip trailing single uppercase letter (e.g., "Groenland B" → "Groenland")
     */
    private function findCountry(string $name): ?Country
    {
        if (isset(self::NAME_ALIASES[$name])) {
            $alias = self::NAME_ALIASES[$name];

            return Country::where('name', $alias)->first()
                ?? Country::whereRaw('LOWER(name) = LOWER(?)', [$alias])->first();
        }

        return Country::where('name', $name)->first()
            ?? Country::whereRaw('LOWER(name) = LOWER(?)', [$name])->first()
            ?? $this->findCountryNormalized($name)
            ?? $this->findCountryStripped($name);
    }

    private function findCountryNormalized(string $name): ?Country
    {
        // Try converting spaces to hyphens: "Sainte Lucie" → "Sainte-Lucie"
        $hyphenated = str_replace(' ', '-', $name);

        return Country::whereRaw('LOWER(name) = LOWER(?)', [$hyphenated])->first();
    }

    private function findCountryStripped(string $name): ?Country
    {
        // Strip trailing " 1", " 2", etc.
        $stripped = preg_replace('/\s+\d+$/', '', $name);

        if ($stripped !== $name) {
            return Country::where('name', $stripped)->first()
                ?? Country::whereRaw('LOWER(name) = LOWER(?)', [$stripped])->first();
        }

        // Strip trailing single uppercase letter like " B"
        $stripped = preg_replace('/\s+[A-Z]$/', '', $name);

        if ($stripped !== $name) {
            return Country::where('name', $stripped)->first()
                ?? Country::whereRaw('LOWER(name) = LOWER(?)', [$stripped])->first();
        }

        return null;
    }

    /**
     * List immediate subfolders of a root folder in Cloudinary.
     *
     * @return Collection<int, string>
     */
    private function listSubfolders(string $root): Collection
    {
        $encoded = implode('/', array_map('rawurlencode', explode('/', $root)));
        $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
            ->get("https://api.cloudinary.com/v1_1/{$this->cloudName}/folders/{$encoded}");

        if ($response->failed()) {
            $this->error('Failed to list folders: '.$response->body());

            return collect();
        }

        return collect($response->json('folders', []))->pluck('path');
    }

    /**
     * Fetch all videos from the given list of Cloudinary folders.
     *
     * @param  Collection<int, string>  $folderPaths
     * @return Collection<int, array<string, mixed>>
     */
    private function fetchVideosFromFolders(Collection $folderPaths): Collection
    {
        $all = collect();

        foreach ($folderPaths as $folder) {
            $this->line("  Fetching «{$folder}»…");
            $resources = $this->searchVideos("resource_type:video AND folder=\"{$folder}\"");
            $all = $all->merge($resources);
        }

        return $all;
    }

    /**
     * Search Cloudinary resources with a given expression, handling pagination.
     *
     * @return array<int, array<string, mixed>>
     */
    private function searchVideos(string $expression): array
    {
        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/resources/search";
        $resources = [];
        $nextCursor = null;

        do {
            $payload = ['expression' => $expression, 'max_results' => 500];

            if ($nextCursor) {
                $payload['next_cursor'] = $nextCursor;
            }

            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->post($url, $payload);

            if ($response->failed()) {
                $this->error('Cloudinary search error: '.$response->body());
                break;
            }

            $data = $response->json();
            $resources = array_merge($resources, $data['resources'] ?? []);
            $nextCursor = $data['next_cursor'] ?? null;

        } while ($nextCursor);

        return $resources;
    }

    /**
     * Build the Cloudinary thumbnail URL for a video (frame at 0s).
     */
    private function thumbnailUrl(string $publicId): string
    {
        $encoded = implode('/', array_map('rawurlencode', explode('/', $publicId)));

        return "https://res.cloudinary.com/{$this->cloudName}/video/upload/so_0/{$encoded}.jpg";
    }
}
