<?php

namespace App\Console\Commands;

use App\Models\CountryInfo;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('countries:translate-infos
    {--dry-run : Prévisualiser sans écrire en base}')]
#[Description('Traduit en français les noms de langue et les capitales déjà importés')]
class TranslateCountryInfos extends Command
{
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('[DRY RUN] Aucune modification ne sera sauvegardée.');
        }

        $updated = 0;

        CountryInfo::with('country')->each(function (CountryInfo $info) use ($dryRun, &$updated): void {
            $changed = false;

            $capital = $this->translateCapital($info->capital);
            if ($capital !== $info->capital) {
                $this->line("  Capitale : {$info->capital} → {$capital} [{$info->country->name}]");
                $info->capital = $capital;
                $changed = true;
            }

            if (is_array($info->languages)) {
                $translatedLanguages = array_map(function (array $lang): array {
                    $translated = $this->translateLanguageName($lang['code'] ?? null, $lang['name'] ?? null);
                    if ($translated !== ($lang['name'] ?? null)) {
                        $lang['name'] = $translated;
                    }

                    return $lang;
                }, $info->languages);

                if ($translatedLanguages !== $info->languages) {
                    $info->languages = $translatedLanguages;
                    $changed = true;
                }
            }

            if ($changed) {
                $updated++;
                if (! $dryRun) {
                    $info->save();
                }
            }
        });

        $this->newLine();
        $action = $dryRun ? 'À mettre à jour' : 'Mis à jour';
        $this->info("{$action} : {$updated} fiche(s)");

        return self::SUCCESS;
    }

    private function translateCapital(?string $capital): ?string
    {
        if ($capital === null) {
            return null;
        }

        return ImportCountryInfos::CAPITAL_NAMES_FR[$capital] ?? $capital;
    }

    private function translateLanguageName(?string $code, ?string $nameEn): ?string
    {
        if ($code !== null && $code !== '') {
            $fr = ImportCountryInfos::LANGUAGE_NAMES_FR[$code] ?? null;
            if ($fr !== null) {
                return $fr;
            }
        }

        if ($nameEn !== null) {
            return ImportCountryInfos::LANGUAGE_NAMES_EN_TO_FR[$nameEn] ?? $nameEn;
        }

        return $nameEn;
    }
}
