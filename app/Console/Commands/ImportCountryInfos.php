<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\CountryInfo;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

#[Signature('countries:import-infos
    {--iso3=* : Restreindre l\'import aux codes ISO3 fournis}
    {--dry-run : Prévisualiser sans écrire en base}
    {--force : Mettre à jour les fiches déjà existantes}')]
#[Description('Importe capital, langues, population et devise depuis l\'API RESTCountries v5')]
class ImportCountryInfos extends Command
{
    private const REQUEST_DELAY_MICROSECONDS = 100_000;

    private const RESPONSE_FIELDS = 'names.translations.fra,capitals,languages,population,currencies';

    private string $baseUrl;

    private string $apiKey;

    public function handle(): int
    {
        $this->baseUrl = (string) config('services.restcountries.url');
        $this->apiKey = (string) config('services.restcountries.key');

        if ($this->baseUrl === '' || $this->apiKey === '') {
            $this->error('RESTCountries credentials not configured in config/services.php.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $iso3Filter = array_map('strtoupper', (array) $this->option('iso3'));

        if ($dryRun) {
            $this->warn('[DRY RUN] No changes will be saved.');
        }

        $countries = Country::query()
            ->whereNotNull('iso3')
            ->when($iso3Filter, fn ($q) => $q->whereIn('iso3', $iso3Filter))
            ->orderBy('name')
            ->get();

        if ($countries->isEmpty()) {
            $this->warn('No matching countries found.');

            return self::SUCCESS;
        }

        $this->info("Pays à traiter : {$countries->count()}");
        $this->newLine();

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($countries as $country) {
            $existing = $country->info()->exists();

            if ($existing && ! $force) {
                $this->line("  SKIP (info existe) : {$country->name} [{$country->iso3}]");
                $skipped++;

                continue;
            }

            try {
                $payload = $this->fetchCountry($country->iso3);
            } catch (Throwable $e) {
                $errors[] = "{$country->name} [{$country->iso3}] : {$e->getMessage()}";
                $this->error("  ERREUR : {$country->name} [{$country->iso3}] — {$e->getMessage()}");
                usleep(self::REQUEST_DELAY_MICROSECONDS);

                continue;
            }

            if ($payload === null) {
                $errors[] = "{$country->name} [{$country->iso3}] : réponse vide";
                $this->warn("  VIDE : {$country->name} [{$country->iso3}]");
                usleep(self::REQUEST_DELAY_MICROSECONDS);

                continue;
            }

            $attributes = $this->mapPayload($payload);

            $label = $existing ? 'UPDATE' : 'NEW';
            $this->line("  [{$label}] {$country->name} [{$country->iso3}] → ".
                ($attributes['capital'] ?? '—').' | '.
                ($attributes['currency'] ?? '—').' | '.
                ($attributes['population'] !== null ? number_format((int) $attributes['population']) : '—'));

            if (! $dryRun) {
                CountryInfo::updateOrCreate(
                    ['country_id' => $country->id],
                    $attributes,
                );
            }

            $existing ? $updated++ : $imported++;

            usleep(self::REQUEST_DELAY_MICROSECONDS);
        }

        $this->newLine();

        if ($errors) {
            $this->warn(count($errors).' erreur(s) rencontrée(s) :');

            foreach ($errors as $entry) {
                $this->warn("  - {$entry}");
            }

            $this->newLine();
        }

        $action = $dryRun ? 'À importer' : 'Importés';
        $this->info("{$action} : {$imported}  |  Mis à jour : {$updated}  |  Ignorés : {$skipped}  |  Erreurs : ".count($errors));

        return self::SUCCESS;
    }

    /**
     * Fetch a single country payload from the RESTCountries v5 API.
     *
     * @return array<string, mixed>|null
     */
    private function fetchCountry(string $iso3): ?array
    {
        $url = rtrim($this->baseUrl, '/').'/countries/v5/codes.alpha_3/'.rawurlencode($iso3);

        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->timeout(10)
            ->retry(2, 200)
            ->get($url, ['response_fields' => self::RESPONSE_FIELDS]);

        $this->ensureSuccessful($response);

        $object = $response->json('data.objects.0');

        return is_array($object) ? $object : null;
    }

    private function ensureSuccessful(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        throw new \RuntimeException("HTTP {$response->status()} — ".mb_strimwidth((string) $response->body(), 0, 120));
    }

    /**
     * Map the API payload to CountryInfo attributes.
     *
     * @param  array<string, mixed>  $payload
     * @return array{capital: ?string, languages: ?array<int, array<string, ?string>>, population: ?int, currency: ?string, population_year: null}
     */
    private function mapPayload(array $payload): array
    {
        return [
            'capital' => $this->extractCapital($payload),
            'languages' => $this->extractLanguages($payload),
            'population' => isset($payload['population']) ? (int) $payload['population'] : null,
            'currency' => $this->extractCurrencyCode($payload),
            'population_year' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractCapital(array $payload): ?string
    {
        $capitals = $payload['capitals'] ?? [];

        if (! is_array($capitals) || $capitals === []) {
            return null;
        }

        $name = $capitals[0]['name'] ?? null;

        return is_string($name) && $name !== '' ? $name : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, array<string, ?string>>|null
     */
    private function extractLanguages(array $payload): ?array
    {
        $languages = $payload['languages'] ?? [];

        if (! is_array($languages) || $languages === []) {
            return null;
        }

        $mapped = [];

        foreach ($languages as $language) {
            if (! is_array($language)) {
                continue;
            }

            $mapped[] = [
                'code' => $language['iso639_1'] ?? $language['bcp47'] ?? null,
                'name' => $language['name'] ?? null,
                'native_name' => $language['native_name'] ?? null,
            ];
        }

        return $mapped === [] ? null : $mapped;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractCurrencyCode(array $payload): ?string
    {
        $currencies = $payload['currencies'] ?? [];

        if (! is_array($currencies) || $currencies === []) {
            return null;
        }

        $code = $currencies[0]['code'] ?? null;

        return is_string($code) && $code !== '' ? $code : null;
    }
}
