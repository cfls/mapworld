<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\CountrySignLanguage;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('countries:import-sign-language-list
    {--base= : Chemin JSON base (défaut docs/referencias/langues_des_signes.json)}
    {--details= : Chemin JSON détaillé (défaut docs/referencias/langues_de_signes_detaillees.json)}
    {--dry-run : Prévisualiser sans écrire en base}
    {--fresh : Vider country_sign_languages avant de réimporter}')]
#[Description('Peuple country_sign_languages : base 195 langues + overrides détaillés avec sigle et année')]
class ImportSignLanguageList extends Command
{
    /**
     * Correspondance nom fichier → nom en base (countries.name).
     *
     * @var array<string, string>
     */
    private const NAME_ALIASES = [
        'Birmanie (Myanmar)' => 'Myanmar',
        'Biélorussie' => 'Bélarus',
        'Cabo Verde' => 'Cap-Vert',
        'Congo (Brazzaville)' => 'Congo',
        'El Salvador' => 'Salvador',
        'Îles Salomon' => 'Salomon',
    ];

    public function handle(): int
    {
        $basePath = (string) ($this->option('base')
            ?: base_path('docs/referencias/langues_des_signes.json'));
        $detailsPath = (string) ($this->option('details')
            ?: base_path('docs/referencias/langues_de_signes_detaillees.json'));

        foreach (['base' => $basePath, 'détaillé' => $detailsPath] as $label => $path) {
            if (! is_file($path)) {
                $this->error("Fichier {$label} introuvable : {$path}");

                return self::FAILURE;
            }
        }

        $base = json_decode((string) file_get_contents($basePath), true, flags: JSON_THROW_ON_ERROR);
        $details = json_decode((string) file_get_contents($detailsPath), true, flags: JSON_THROW_ON_ERROR);

        $dryRun = (bool) $this->option('dry-run');
        $fresh = (bool) $this->option('fresh');

        if ($dryRun) {
            $this->warn('[DRY RUN] Aucun changement ne sera enregistré.');
        }

        $countriesByNormName = Country::query()
            ->get(['id', 'name'])
            ->keyBy(fn (Country $c) => $this->normalize($c->name));

        // Regrouper les entrées détaillées par pays (peut contenir plusieurs entrées par pays).
        $detailsByCountryId = [];
        $detailsNotFound = [];
        foreach ($details as $row) {
            $pays = (string) ($row['pays'] ?? '');
            if ($pays === '') {
                continue;
            }
            $lookup = self::NAME_ALIASES[$pays] ?? $pays;
            $country = $countriesByNormName->get($this->normalize($lookup));

            if (! $country) {
                $detailsNotFound[] = $pays;

                continue;
            }

            $detailsByCountryId[$country->id][] = [
                'nom' => (string) $row['nom'],
                'sigle' => $row['sigle'] ?? null,
                'annee_de_reconnaissance' => isset($row['annee']) ? (int) $row['annee'] : null,
            ];
        }

        $baseCreated = 0;
        $overrideCreated = 0;
        $baseNotFound = [];

        DB::beginTransaction();
        try {
            if ($fresh && ! $dryRun) {
                CountrySignLanguage::query()->delete();
                $this->warn('country_sign_languages vidée.');
            }

            // 1) Base : 1 ligne par pays, sauf si un override détaillé existe (traité en étape 2).
            foreach ($base as $row) {
                $pays = (string) ($row['pays'] ?? '');
                if ($pays === '') {
                    continue;
                }
                $lookup = self::NAME_ALIASES[$pays] ?? $pays;
                $country = $countriesByNormName->get($this->normalize($lookup));

                if (! $country) {
                    $baseNotFound[] = $pays;

                    continue;
                }

                if (isset($detailsByCountryId[$country->id])) {
                    // Sera géré à l'étape 2.
                    continue;
                }

                $parsed = $this->parseSigleFromNom((string) ($row['langue'] ?? ''));
                $payload = [
                    'nom' => $parsed['nom'],
                    'sigle' => $parsed['sigle'],
                    'annee_de_reconnaissance' => isset($row['annee']) ? (int) $row['annee'] : null,
                ];

                if (! $dryRun) {
                    CountrySignLanguage::updateOrCreate(
                        ['country_id' => $country->id, 'nom' => $payload['nom']],
                        $payload,
                    );
                }
                $baseCreated++;
            }

            // 2) Overrides détaillés : purge par pays puis réinsère toutes les entrées.
            foreach ($detailsByCountryId as $countryId => $entries) {
                if (! $dryRun) {
                    CountrySignLanguage::query()
                        ->where('country_id', $countryId)
                        ->delete();

                    foreach ($entries as $entry) {
                        CountrySignLanguage::create([
                            'country_id' => $countryId,
                            'nom' => $entry['nom'],
                            'sigle' => $entry['sigle'],
                            'annee_de_reconnaissance' => $entry['annee_de_reconnaissance'],
                        ]);
                    }
                }
                $overrideCreated += count($entries);
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Échec : {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->newLine();

        if ($baseNotFound !== []) {
            $this->warn(count($baseNotFound).' pays base non trouvés :');
            foreach ($baseNotFound as $n) {
                $this->warn("  - {$n}");
            }
        }
        if ($detailsNotFound !== []) {
            $this->warn(count($detailsNotFound).' pays détaillés non trouvés :');
            foreach ($detailsNotFound as $n) {
                $this->warn("  - {$n}");
            }
        }

        $this->info("Base insérées : {$baseCreated}  |  Overrides détaillées : {$overrideCreated}");

        return self::SUCCESS;
    }

    /**
     * Extrait un sigle entre parenthèses en fin de chaîne s'il ressemble à un sigle
     * (majuscules/chiffres, sans espaces, longueur 2-10).
     *
     * @return array{nom: string, sigle: ?string}
     */
    private function parseSigleFromNom(string $nom): array
    {
        $nom = trim($nom);
        if ($nom === '') {
            return ['nom' => '', 'sigle' => null];
        }

        if (preg_match('/^(.*?)\s*\(([^\s()]{2,10})\)\s*$/u', $nom, $m)) {
            $candidate = $m[2];
            // Sigle si tout majuscule / chiffres / diacritiques majuscules.
            if (preg_match('/^[\p{Lu}\p{N}]+$/u', $candidate) || preg_match('/^[A-Z][A-Za-z0-9]{1,9}$/u', $candidate)) {
                return ['nom' => trim($m[1]), 'sigle' => $candidate];
            }
        }

        return ['nom' => $nom, 'sigle' => null];
    }

    private function normalize(string $value): string
    {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $ascii = is_string($ascii) ? $ascii : $value;

        return preg_replace('/[^a-z0-9]+/', '', mb_strtolower($ascii)) ?? '';
    }
}
