<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\CountryInfo;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('countries:import-sign-languages
    {--source= : Chemin vers le fichier JSON source (par défaut docs/referencias/langues_des_signes.json)}
    {--dry-run : Prévisualiser sans écrire en base}
    {--force : Écraser les valeurs existantes (langue_de_signes / annee_de_reconnaissance)}')]
#[Description('Importe la langue des signes et son année de reconnaissance officielle depuis le fichier de référence')]
class ImportSignLanguages extends Command
{
    /**
     * Correspondance nom Excel → nom en base (countries.name).
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
        $source = (string) ($this->option('source')
            ?: base_path('docs/referencias/langues_des_signes.json'));

        if (! is_file($source)) {
            $this->error("Fichier source introuvable : {$source}");

            return self::FAILURE;
        }

        $raw = file_get_contents($source);
        $rows = json_decode((string) $raw, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($rows) || $rows === []) {
            $this->error('Fichier source vide ou invalide.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        if ($dryRun) {
            $this->warn('[DRY RUN] Aucun changement ne sera enregistré.');
        }

        $countriesByNormName = Country::query()
            ->get(['id', 'name'])
            ->keyBy(fn (Country $c) => $this->normalize($c->name));

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $notFound = [];

        foreach ($rows as $row) {
            $pays = (string) ($row['pays'] ?? '');
            $langue = $row['langue'] ?? null;
            $annee = $row['annee'] ?? null;

            if ($pays === '') {
                continue;
            }

            $lookupName = self::NAME_ALIASES[$pays] ?? $pays;
            $country = $countriesByNormName->get($this->normalize($lookupName));

            if (! $country) {
                $notFound[] = $pays;

                continue;
            }

            /** @var CountryInfo $info */
            $info = CountryInfo::firstOrNew(['country_id' => $country->id]);
            $exists = $info->exists;

            $shouldWriteLangue = $langue !== null
                && ($force || $info->langue_de_signes === null || $info->langue_de_signes === '');
            $shouldWriteAnnee = $annee !== null
                && ($force || $info->annee_de_reconnaissance === null);

            if (! $shouldWriteLangue && ! $shouldWriteAnnee && $exists) {
                $this->line("  SKIP : {$country->name} (déjà renseigné)");
                $skipped++;

                continue;
            }

            if ($shouldWriteLangue) {
                $info->langue_de_signes = (string) $langue;
            }

            if ($shouldWriteAnnee) {
                $info->annee_de_reconnaissance = (int) $annee;
            }

            $label = $exists ? 'UPDATE' : 'NEW';
            $this->line("  [{$label}] {$country->name} → ".
                ($info->langue_de_signes ?? '—').
                ($info->annee_de_reconnaissance !== null ? " ({$info->annee_de_reconnaissance})" : ''));

            if (! $dryRun) {
                $info->save();
            }

            $exists ? $updated++ : $created++;
        }

        $this->newLine();

        if ($notFound !== []) {
            $this->warn(count($notFound).' pays du fichier non trouvés en base :');
            foreach ($notFound as $name) {
                $this->warn("  - {$name}");
            }
            $this->newLine();
        }

        $action = $dryRun ? 'À créer' : 'Créés';
        $this->info("{$action} : {$created}  |  Mis à jour : {$updated}  |  Ignorés : {$skipped}  |  Non trouvés : ".count($notFound));

        return self::SUCCESS;
    }

    private function normalize(string $value): string
    {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $ascii = is_string($ascii) ? $ascii : $value;

        return preg_replace('/[^a-z0-9]+/', '', mb_strtolower($ascii)) ?? '';
    }
}
