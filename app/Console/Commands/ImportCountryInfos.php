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

    /** @var array<string, string> ISO 639-1 / BCP-47 code → nom en français */
    public const LANGUAGE_NAMES_FR = [
        'af' => 'Afrikaans',
        'am' => 'Amharique',
        'ar' => 'Arabe',
        'ay' => 'Aymara',
        'az' => 'Azerbaïdjanais',
        'be' => 'Biélorusse',
        'bg' => 'Bulgare',
        'bi' => 'Bichelamar',
        'bn' => 'Bengali',
        'bo' => 'Tibétain',
        'bs' => 'Bosnien',
        'ca' => 'Catalan',
        'ce' => 'Tchétchène',
        'ch' => 'Chamorro',
        'cs' => 'Tchèque',
        'cy' => 'Gallois',
        'da' => 'Danois',
        'de' => 'Allemand',
        'dv' => 'Maldivien',
        'dz' => 'Dzongkha',
        'el' => 'Grec',
        'en' => 'Anglais',
        'es' => 'Espagnol',
        'et' => 'Estonien',
        'eu' => 'Basque',
        'fa' => 'Persan',
        'fi' => 'Finnois',
        'fj' => 'Fidjien',
        'fo' => 'Féroïen',
        'fr' => 'Français',
        'ga' => 'Irlandais',
        'gd' => 'Gaélique écossais',
        'gl' => 'Galicien',
        'gn' => 'Guarani',
        'gv' => 'Mannois',
        'he' => 'Hébreu',
        'hi' => 'Hindi',
        'ho' => 'Hiri Motu',
        'hr' => 'Croate',
        'ht' => 'Créole haïtien',
        'hu' => 'Hongrois',
        'hy' => 'Arménien',
        'hz' => 'Héréro',
        'id' => 'Indonésien',
        'is' => 'Islandais',
        'it' => 'Italien',
        'ja' => 'Japonais',
        'ka' => 'Géorgien',
        'kg' => 'Kongo',
        'kk' => 'Kazakh',
        'kl' => 'Groenlandais',
        'km' => 'Khmer',
        'ko' => 'Coréen',
        'ku' => 'Kurde',
        'ky' => 'Kirghiz',
        'la' => 'Latin',
        'lb' => 'Luxembourgeois',
        'ln' => 'Lingala',
        'lo' => 'Laotien',
        'lt' => 'Lituanien',
        'lv' => 'Letton',
        'mg' => 'Malgache',
        'mh' => 'Marshallais',
        'mi' => 'Maori',
        'mk' => 'Macédonien',
        'mn' => 'Mongol',
        'ms' => 'Malais',
        'mt' => 'Maltais',
        'my' => 'Birman',
        'na' => 'Nauruan',
        'nb' => 'Norvégien bokmål',
        'nd' => 'Ndébélé du Nord',
        'ne' => 'Népalais',
        'ng' => 'Ndonga',
        'nl' => 'Néerlandais',
        'nn' => 'Norvégien nynorsk',
        'nr' => 'Ndébélé du Sud',
        'ny' => 'Chichewa',
        'oc' => 'Occitan',
        'pl' => 'Polonais',
        'ps' => 'Pachto',
        'pt' => 'Portugais',
        'qu' => 'Quechua',
        'rm' => 'Romanche',
        'rn' => 'Kirundi',
        'ro' => 'Roumain',
        'ru' => 'Russe',
        'rw' => 'Kinyarwanda',
        'se' => 'Same du Nord',
        'sg' => 'Sango',
        'si' => 'Cingalais',
        'sk' => 'Slovaque',
        'sl' => 'Slovène',
        'sm' => 'Samoan',
        'sn' => 'Shona',
        'so' => 'Somali',
        'sq' => 'Albanais',
        'sr' => 'Serbe',
        'ss' => 'Swati',
        'st' => 'Sotho du Sud',
        'sv' => 'Suédois',
        'sw' => 'Swahili',
        'ta' => 'Tamoul',
        'tg' => 'Tadjik',
        'th' => 'Thaï',
        'ti' => 'Tigrigna',
        'tk' => 'Turkmène',
        'tn' => 'Tswana',
        'to' => 'Tongien',
        'tr' => 'Turc',
        'ts' => 'Tsonga',
        'uk' => 'Ukrainien',
        'ur' => 'Ourdou',
        'uz' => 'Ouzbek',
        've' => 'Venda',
        'vi' => 'Vietnamien',
        'xh' => 'Xhosa',
        'zh' => 'Chinois',
        'zu' => 'Zoulou',
        'sco' => 'Scots',
    ];

    /** @var array<string, string> Nom anglais de la langue → nom en français (pour les langues sans code ISO 639-1) */
    public const LANGUAGE_NAMES_EN_TO_FR = [
        'Afrikaans' => 'Afrikaans',
        'Amharic' => 'Amharique',
        'Arabic' => 'Arabe',
        'Aramaic' => 'Araméen',
        'Aymara' => 'Aymara',
        'Azerbaijani' => 'Azerbaïdjanais',
        'Belarusian' => 'Biélorusse',
        'Belize Kriol' => 'Créole bélizien',
        'Bengali' => 'Bengali',
        'Bichelamar' => 'Bichelamar',
        'Bislama' => 'Bichelamar',
        'Bosnian' => 'Bosnien',
        'Bulgarian' => 'Bulgare',
        'Burmese' => 'Birman',
        'Carolinian' => 'Carolinien',
        'Catalan' => 'Catalan',
        'Central Kurdish' => 'Kurde central (Sorani)',
        'Chamorro' => 'Chamorro',
        'Chechen' => 'Tchétchène',
        'Chichewa' => 'Chichewa',
        'Chinese' => 'Chinois',
        'Croatian' => 'Croate',
        'Czech' => 'Tchèque',
        'Danish' => 'Danois',
        'Dari' => 'Dari',
        'Divehi' => 'Maldivien',
        'Dutch' => 'Néerlandais',
        'Dzongkha' => 'Dzongkha',
        'English' => 'Anglais',
        'Estonian' => 'Estonien',
        'Faroese' => 'Féroïen',
        'Fijian' => 'Fidjien',
        'Fiji Hindi' => 'Hindi fidjien',
        'Filipino' => 'Filipino',
        'Finnish' => 'Finnois',
        'French' => 'Français',
        'Galician' => 'Galicien',
        'Gaelic' => 'Gaélique',
        'Georgian' => 'Géorgien',
        'German' => 'Allemand',
        'Gilbertese' => 'Gilbertin',
        'Greek' => 'Grec',
        'Greenlandic' => 'Groenlandais',
        'Guarani' => 'Guarani',
        'Haitian Creole' => 'Créole haïtien',
        'Hassaniya Arabic' => 'Arabe hassanya',
        'Hebrew' => 'Hébreu',
        'Herero' => 'Héréro',
        'Hindi' => 'Hindi',
        'Hiri Motu' => 'Hiri Motu',
        'Hungarian' => 'Hongrois',
        'Icelandic' => 'Islandais',
        'Indonesian' => 'Indonésien',
        'Irish' => 'Irlandais',
        'Italian' => 'Italien',
        'Jamaican Patois' => 'Créole jamaïcain',
        'Japanese' => 'Japonais',
        'Kazakh' => 'Kazakh',
        'Khmer' => 'Khmer',
        'Khoisan languages' => 'Langues khoïsan',
        'Kinyarwanda' => 'Kinyarwanda',
        'Kirundi' => 'Kirundi',
        'Kongo' => 'Kongo',
        'Korean' => 'Coréen',
        'Kurdish' => 'Kurde',
        'Kwangali' => 'Kwangali',
        'Kyrgyz' => 'Kirghiz',
        'Lao' => 'Laotien',
        'Latin' => 'Latin',
        'Latvian' => 'Letton',
        'Lingala' => 'Lingala',
        'Lithuanian' => 'Lituanien',
        'Lozi' => 'Lozi',
        'Luba-Lulua' => 'Luba-Lulua',
        'Lule Sami' => 'Same de Lule',
        'Luxembourgish' => 'Luxembourgeois',
        'Macedonian' => 'Macédonien',
        'Malagasy' => 'Malgache',
        'Malay' => 'Malais',
        'Maltese' => 'Maltais',
        'Manx' => 'Mannois',
        'Maori' => 'Maori',
        'Marshallese' => 'Marshallais',
        'Mauritian Creole' => 'Créole mauricien',
        'Mongolian' => 'Mongol',
        'Montenegrin' => 'Monténégrin',
        'Nauruan' => 'Nauruan',
        'Ndau' => 'Ndau',
        'Ndonga' => 'Ndonga',
        'Nepali' => 'Népalais',
        'New Zealand Sign Language' => 'Langue des signes néo-zélandaise',
        'Ngazidja Comorian' => 'Comorien',
        'Northern Ndebele' => 'Ndébélé du Nord',
        'Northern Sami' => 'Same du Nord',
        'Northern Sotho' => 'Sotho du Nord',
        'Norwegian Bokmål' => 'Norvégien bokmål',
        'Norwegian Nynorsk' => 'Norvégien nynorsk',
        'Occitan' => 'Occitan',
        'Aranese' => 'Aranais',
        'Palauan' => 'Palauan',
        'Pashto' => 'Pachto',
        'Persian' => 'Persan',
        'Polish' => 'Polonais',
        'Portuguese' => 'Portugais',
        'Quechua' => 'Quechua',
        'Romanian' => 'Roumain',
        'Romansh' => 'Romanche',
        'Russian' => 'Russe',
        'Samoan' => 'Samoan',
        'Sango' => 'Sango',
        'Scottish Gaelic' => 'Gaélique écossais',
        'Scots' => 'Scots',
        'Serbian' => 'Serbe',
        'Seychellois Creole' => 'Créole seychellois',
        'Shona' => 'Shona',
        'Sinhala' => 'Cingalais',
        'Slovak' => 'Slovaque',
        'Slovenian' => 'Slovène',
        'Somali' => 'Somali',
        'Sotho' => 'Sotho du Sud',
        'Southern Ndebele' => 'Ndébélé du Sud',
        'Southern Sami' => 'Same du Sud',
        'Spanish' => 'Espagnol',
        'Standard Moroccan Tamazight' => 'Tamazight marocain',
        'Swahili' => 'Swahili',
        'Swazi' => 'Swati',
        'Swedish' => 'Suédois',
        'Tajik' => 'Tadjik',
        'Tamil' => 'Tamoul',
        'Tetum' => 'Tétoum',
        'Thai' => 'Thaï',
        'Tibetan' => 'Tibétain',
        'Tigrinya' => 'Tigrigna',
        'Tok Pisin' => 'Tok Pisin',
        'Tonga (Zambia)' => 'Tonga (Zambie)',
        'Tongan' => 'Tongien',
        'Tsonga' => 'Tsonga',
        'Tswana' => 'Tswana',
        'Turkish' => 'Turc',
        'Turkmen' => 'Turkmène',
        'Tuvaluan' => 'Tuvaluan',
        'Ukrainian' => 'Ukrainien',
        'Upper Guinea Crioulo' => 'Créole de Guinée-Bissau',
        'Urdu' => 'Ourdou',
        'Uzbek' => 'Ouzbek',
        'Venda' => 'Venda',
        'Vietnamese' => 'Vietnamien',
        'Welsh' => 'Gallois',
        'Xhosa' => 'Xhosa',
        'Zimbabwean Sign Language' => 'Langue des signes zimbabwéenne',
        'Zulu' => 'Zoulou',
        'Barwe' => 'Barwe',
        'Kalanga' => 'Kalanga',
        'Basque' => 'Basque',
        'Kongo' => 'Kongo',
        'Kirghiz' => 'Kirghiz',
    ];

    /** @var array<string, string> Code ISO 4217 → nom de la monnaie en français */
    public const CURRENCY_NAMES_FR = [
        'AED' => 'dirham des Émirats arabes unis',
        'AFN' => 'afghani afghan',
        'ALL' => 'lek albanais',
        'AMD' => 'dram arménien',
        'ANG' => 'florin antillais',
        'AOA' => 'kwanza angolais',
        'ARS' => 'peso argentin',
        'AUD' => 'dollar australien',
        'AWG' => 'florin arubais',
        'AZN' => 'manat azerbaïdjanais',
        'BAM' => 'mark convertible de Bosnie-Herzégovine',
        'BBD' => 'dollar de la Barbade',
        'BDT' => 'taka bangladais',
        'BGN' => 'lev bulgare',
        'BHD' => 'dinar bahreïni',
        'BIF' => 'franc burundais',
        'BMD' => 'dollar des Bermudes',
        'BND' => 'dollar de Brunei',
        'BOB' => 'boliviano',
        'BRL' => 'réal brésilien',
        'BSD' => 'dollar des Bahamas',
        'BTN' => 'ngultrum bhoutanais',
        'BWP' => 'pula botswanais',
        'BYN' => 'rouble biélorusse',
        'BZD' => 'dollar de Belize',
        'CAD' => 'dollar canadien',
        'CDF' => 'franc congolais',
        'CHF' => 'franc suisse',
        'CLP' => 'peso chilien',
        'CNY' => 'yuan renminbi',
        'COP' => 'peso colombien',
        'CRC' => 'colón costaricain',
        'CUP' => 'peso cubain',
        'CVE' => 'escudo cap-verdien',
        'CZK' => 'couronne tchèque',
        'DJF' => 'franc djiboutien',
        'DKK' => 'couronne danoise',
        'DOP' => 'peso dominicain',
        'DZD' => 'dinar algérien',
        'EGP' => 'livre égyptienne',
        'ERN' => 'nakfa érythréen',
        'ETB' => 'birr éthiopien',
        'EUR' => 'euro',
        'FJD' => 'dollar fidjien',
        'FKP' => 'livre des Malouines',
        'GBP' => 'livre sterling',
        'GEL' => 'lari géorgien',
        'GHS' => 'cedi ghanéen',
        'GIP' => 'livre de Gibraltar',
        'GMD' => 'dalasi gambien',
        'GNF' => 'franc guinéen',
        'GTQ' => 'quetzal guatémaltèque',
        'GYD' => 'dollar guyanien',
        'HKD' => 'dollar de Hong Kong',
        'HNL' => 'lempira hondurien',
        'HTG' => 'gourde haïtienne',
        'HUF' => 'forint hongrois',
        'IDR' => 'roupie indonésienne',
        'ILS' => 'nouveau shekel israélien',
        'INR' => 'roupie indienne',
        'IQD' => 'dinar irakien',
        'IRR' => 'rial iranien',
        'ISK' => 'couronne islandaise',
        'JMD' => 'dollar jamaïcain',
        'JOD' => 'dinar jordanien',
        'JPY' => 'yen japonais',
        'KES' => 'shilling kényan',
        'KGS' => 'som kirghiz',
        'KHR' => 'riel cambodgien',
        'KMF' => 'franc comorien',
        'KPW' => 'won nord-coréen',
        'KRW' => 'won sud-coréen',
        'KWD' => 'dinar koweïtien',
        'KYD' => 'dollar des îles Caïmans',
        'KZT' => 'tenge kazakh',
        'LAK' => 'kip laotien',
        'LBP' => 'livre libanaise',
        'LKR' => 'roupie sri-lankaise',
        'LRD' => 'dollar libérien',
        'LSL' => 'loti lésothien',
        'LYD' => 'dinar libyen',
        'MAD' => 'dirham marocain',
        'MDL' => 'leu moldave',
        'MGA' => 'ariary malgache',
        'MKD' => 'denar macédonien',
        'MMK' => 'kyat birman',
        'MNT' => 'tugrik mongol',
        'MOP' => 'pataca macanais',
        'MRU' => 'ouguiya mauritanien',
        'MUR' => 'roupie mauricienne',
        'MVR' => 'rufiyaa maldivienne',
        'MWK' => 'kwacha malawite',
        'MXN' => 'peso mexicain',
        'MYR' => 'ringgit malaisien',
        'MZN' => 'metical mozambicain',
        'NAD' => 'dollar namibien',
        'NGN' => 'naira nigérian',
        'NIO' => 'córdoba nicaraguayen',
        'NOK' => 'couronne norvégienne',
        'NPR' => 'roupie népalaise',
        'NZD' => 'dollar néo-zélandais',
        'OMR' => 'rial omanais',
        'PAB' => 'balboa panaméen',
        'PEN' => 'sol péruvien',
        'PGK' => 'kina papou-néo-guinéen',
        'PHP' => 'peso philippin',
        'PKR' => 'roupie pakistanaise',
        'PLN' => 'zloty polonais',
        'PYG' => 'guaraní paraguayen',
        'QAR' => 'riyal qatarien',
        'RON' => 'leu roumain',
        'RSD' => 'dinar serbe',
        'RUB' => 'rouble russe',
        'RWF' => 'franc rwandais',
        'SAR' => 'riyal saoudien',
        'SBD' => 'dollar des Salomon',
        'SCR' => 'roupie des Seychelles',
        'SDG' => 'livre soudanaise',
        'SEK' => 'couronne suédoise',
        'SGD' => 'dollar de Singapour',
        'SHP' => 'livre de Sainte-Hélène',
        'SLE' => 'leone sierra-léonais',
        'SOS' => 'shilling somalien',
        'SRD' => 'dollar du Suriname',
        'SSP' => 'livre sud-soudanaise',
        'STN' => 'dobra de São Tomé-et-Príncipe',
        'SYP' => 'livre syrienne',
        'SZL' => 'lilangeni swazilandais',
        'THB' => 'baht thaïlandais',
        'TJS' => 'somoni tadjik',
        'TMT' => 'manat turkmène',
        'TND' => 'dinar tunisien',
        'TOP' => 'paʻanga tongien',
        'TRY' => 'lire turque',
        'TTD' => 'dollar de Trinité-et-Tobago',
        'TWD' => 'nouveau dollar taïwanais',
        'TZS' => 'shilling tanzanien',
        'UAH' => 'hryvnia ukrainienne',
        'UGX' => 'shilling ougandais',
        'USD' => 'dollar américain',
        'UYU' => 'peso uruguayen',
        'UZS' => 'soum ouzbek',
        'VES' => 'bolívar vénézuélien',
        'VND' => 'dong vietnamien',
        'VUV' => 'vatu vanuatuan',
        'WST' => 'tālā samoan',
        'XAF' => "franc CFA d'Afrique centrale",
        'XCD' => 'dollar des Caraïbes orientales',
        'XOF' => "franc CFA d'Afrique de l'Ouest",
        'XPF' => 'franc CFP',
        'YER' => 'rial yéménite',
        'ZAR' => 'rand sud-africain',
        'ZMW' => 'kwacha zambien',
        'ZWL' => 'dollar zimbabwéen',
    ];

    /** @var array<string, string> Nom anglais de la capitale → nom en français */
    public const CAPITAL_NAMES_FR = [
        'Abu Dhabi' => 'Abou Dabi',
        'Addis Ababa' => 'Addis-Abeba',
        'Algiers' => 'Alger',
        'Athens' => 'Athènes',
        'Baghdad' => 'Bagdad',
        'Beijing' => 'Pékin',
        'Beirut' => 'Beyrouth',
        'Brussels' => 'Bruxelles',
        'Bucharest' => 'Bucarest',
        'Cairo' => 'Le Caire',
        'Copenhagen' => 'Copenhague',
        'Damascus' => 'Damas',
        'Guatemala City' => 'Guatemala',
        'Hanoi' => 'Hanoï',
        'Havana' => 'La Havane',
        'Jakarta' => 'Djakarta',
        'Jerusalem' => 'Jérusalem',
        'Kabul' => 'Kaboul',
        'Kathmandu' => 'Katmandou',
        'Kuwait City' => 'Koweït',
        'Kyiv' => 'Kiev',
        'Lisbon' => 'Lisbonne',
        'London' => 'Londres',
        'Mexico City' => 'Mexico',
        'Moscow' => 'Moscou',
        'Muscat' => 'Mascate',
        'Nicosia' => 'Nicosie',
        'Panama City' => 'Panama',
        'Seoul' => 'Séoul',
        'Singapore' => 'Singapour',
        'Tehran' => 'Téhéran',
        'Ulan Bator' => 'Oulan-Bator',
        'Vatican City' => 'Cité du Vatican',
        'Vienna' => 'Vienne',
        'Warsaw' => 'Varsovie',
    ];

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
            'currency' => $this->extractCurrency($payload),
            'currency_code' => $this->extractCurrencyCode($payload),
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

        if (! is_string($name) || $name === '') {
            return null;
        }

        return self::CAPITAL_NAMES_FR[$name] ?? $name;
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

            $code = $language['iso639_1'] ?? $language['bcp47'] ?? null;
            $nameEn = $language['name'] ?? null;
            $nameFr = ($code !== null && $code !== '' ? (self::LANGUAGE_NAMES_FR[$code] ?? null) : null)
                ?? (is_string($nameEn) ? (self::LANGUAGE_NAMES_EN_TO_FR[$nameEn] ?? null) : null)
                ?? $nameEn;

            $mapped[] = [
                'code' => $code,
                'name' => $nameFr,
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

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractCurrency(array $payload): ?string
    {
        $currencies = $payload['currencies'] ?? [];

        if (! is_array($currencies) || $currencies === []) {
            return null;
        }

        $code = $currencies[0]['code'] ?? null;

        if (! is_string($code) || $code === '') {
            return null;
        }

        return self::CURRENCY_NAMES_FR[$code] ?? ($currencies[0]['name'] ?? $code);
    }
}
