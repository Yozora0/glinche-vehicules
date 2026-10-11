<?php

namespace App\Services\Glinche;

class VehicleDetailPresenter
{
    private const NBSP = "\u{00A0}";

    public const BODY_LABELS = [
        'CAMPING_CAR' => 'Camping-car',
        'CAR_CARRYING_PLATFORM' => 'Plateau porte-voiture',
        'CITY_CAR' => 'Citadine',
        'COLLECTION' => 'Collection',
        'CONVERTED_VAN' => 'Van aménagé',
        'CONVERTIBLE' => 'Cabriolet',
        'COUPE' => 'Coupé',
        'FLATBED' => 'Plateau',
        'LARGE_VOLUME' => 'Grand volume',
        'MINIBUS' => 'Minibus',
        'MINIVAN' => 'Monospace',
        'MPV' => 'Fourgonnette',
        'OTHER' => 'Autre',
        'PRESTIGE' => 'Prestige',
        'REFRIGERATED' => 'Frigorifique',
        'SEDAN' => 'Berline',
        'STATION_WAGON' => 'Break',
        'SUV' => 'SUV / Tout-terrain',
        'TIPPER' => 'Benne',
        'TOW_TRUCK' => 'Plateau dépanneuse',
        'QUADRICYCLE' => 'Quadricycle',
        'TRUCK' => 'Fourgon',
        'WAV' => 'TPMR',
    ];

    public const GENRE_LABELS = [
        'VP' => 'Véhicule particulier',
        'CTTE' => 'Véhicule utilitaire',
        'DERIVVP' => 'Véhicule utilitaire (dérivé VP)',
        'VTSU' => 'Véhicule utilitaire',
        'CAM' => 'Camion (> 3,5 t)',
        'TCP' => 'Bus (> 3,5 t)',
        'TRR' => 'Tracteur routier',
        'MTL' => 'Moto',
        'MTT1' => 'Moto',
        'MTT2' => 'Moto',
        'CL' => 'Cyclomoteur',
        'QM' => 'Quadricycle à moteur',
        'TRA' => 'Engin agricole',
        'QUAD' => 'Quad',
        'MAGA' => 'Engin agricole',
        'REM' => 'Remorque',
        'SREM' => 'Semi-remorque',
        'RESP' => 'Caravane',
        'TM' => 'Tricycle à moteur',
        'VASP' => 'Véhicule spécialisé',
    ];

    public const WARRANTY_LABELS = [
        'NO_WARRANTY' => 'Sans garantie',
        'SIX_MONTHS_LIMITED' => '6 mois, kilométrage limité',
        'SIX_MONTHS_UNLIMITED' => '6 mois, kilométrage illimité',
        'TWELVE_MONTHS_UNLIMITED' => '12 mois, kilométrage illimité',
        'EIGHTEEN_MONTHS_UNLIMITED' => '18 mois, kilométrage illimité',
        'TWENTY_FOUR_MONTHS_UNLIMITED' => '24 mois, kilométrage illimité',
        'THIRTY_SIX_MONTHS_UNLIMITED' => '36 mois, kilométrage illimité',
        'THIRTY_SIX_MONTHS_LIMITED' => '36 mois, kilométrage limité',
        'THIRTY_SIX_MONTHS_CONSTRUCTOR' => '36 mois constructeur',
        'FOURTY_EIGHT_MONTHS_UNLIMITED' => '48 mois, kilométrage illimité',
        'FOURTY_EIGHT_MONTHS_UNLIMITED_TESLA' => '48 mois, kilométrage illimité (Tesla)',
        'SIXTY_MONTHS_UNLIMITED_HYUNDAI' => '60 mois, kilométrage illimité (Hyundai)',
        'SIXTY_MONTHS_LIMITED_ISU' => '60 mois, kilométrage limité (Isuzu)',
        'SIXTY_MONTHS_LIMITED_NISSAN' => '60 mois, kilométrage limité (Nissan)',
        'SIXTY_MONTHS_UNLIMITED_FORD' => '60 mois, kilométrage illimité (Ford)',
        'SIXTY_MONTHS_UNLIMITED' => '60 mois, kilométrage illimité',
        'EIGHTY_FOUR_MONTHS_LIMITED_KIA' => '84 mois, kilométrage limité (Kia)',
        'EIGHTY_FOUR_MONTHS_UNLIMITED' => '84 mois, kilométrage illimité',
    ];

    public const ORIGIN_LABELS = [
        'LOAC' => 'Ex-LLD/LOA particulier',
        'LOAE' => 'Ex-LLD/LOA entreprise',
        'LOU' => 'Ex-loueur',
        'REP' => 'Reprise client',
        'DIR' => 'Ex-véhicule de direction',
        'COM' => 'Facture de commission',
    ];

    public const MAIN_COLOR_LABELS = [
        'BEIGE' => 'Beige',
        'BLACK' => 'Noir',
        'BLUE' => 'Bleu',
        'BROWN' => 'Marron',
        'GREEN' => 'Vert',
        'GREY' => 'Gris',
        'ORANGE' => 'Orange',
        'RED' => 'Rouge',
        'WHITE' => 'Blanc',
        'YELLOW' => 'Jaune',
    ];

    public const INTERIOR_LABELS = [
        'ALC' => 'Alcantara',
        'ALCC' => 'Alcantara / Cuir',
        'CHA' => 'Chaîne et trame',
        'CUI' => 'Cuir',
        'CUITR' => 'Cuir / Tissu Rapton',
        'CUIA' => 'Cuir / Alcantara',
        'SIM' => 'Cuir / Simili cuir',
        'DEEG' => 'Deep Garnet / Ebony',
        'DRA' => 'Drap',
        'JEA' => 'Jean',
        'TEPT' => 'Mi-TEP / Mi-tissu',
        'MIC' => 'Microfleece',
        'RHO' => 'Rhombus',
        'SEM' => 'Semi-cuir',
        'SEMT' => 'Semi-cuir / Tissu',
        'SIMI' => 'Simili',
        'SIMC' => 'Simili cuir',
        'SIMT' => 'Simili cuir / Tissu',
        'SIMA' => 'Similicuir / Alcantara',
        'SIMD' => 'Similicuir / Dinamica',
        'SIMV' => 'Similicuir / Velours',
        'SIMAR' => 'Similicuir / Artico',
        'TEP' => 'TEP',
        'TEPA' => 'TEP / Alcantara',
        'TEPAT' => 'TEP / Alcantara / Tissu',
        'TIS' => 'Tissu',
        'TISA' => 'Tissu / Alcantara',
        'TISM' => 'Tissu / Microfleece',
        'TISV' => 'Tissu / Velours',
        'TISC' => 'Tissu / Cuir',
        'TISL' => 'Tissu GT-Line',
        'TWE' => 'Tweed',
        'VEL' => 'Velours',
    ];

    public const TAX_LABELS = [
        'VAT' => 'TVA 20 %',
        'VAT_MARGIN' => 'TVA sur marge',
        'NO_TAX' => 'Exonéré de TVA',
        'EXO' => 'Exonéré de TVA',
        'VAT_REDUCED' => 'TVA 5,5 %',
        'TCA' => 'TCA',
    ];

    public const CATEGORY_LABELS = [
        'SECURITY' => 'Sécurité et aide à la conduite',
        'CONFORT' => 'Confort et multimédia',
        'INOUT' => 'Extérieur et intérieur',
        'ELEMDIST' => 'Éléments distinctifs',
        'OTHER' => 'Autres',
    ];

    public function present(array $advert): array
    {
        $v = is_array($advert['vehicle'] ?? null) ? $advert['vehicle'] : [];
        $prices = is_array($v['prices'] ?? null) ? $v['prices'] : [];

        $sections = [
            $this->section('Informations générales', [
                'Type d\'offre' => AdvertMapper::offerTypeLabel($this->str($v['type'] ?? null)),
                'Carrosserie' => $this->label(self::BODY_LABELS, $v['body'] ?? null),
                'Genre' => $this->label(self::GENRE_LABELS, $v['genre'] ?? null),
                'Année' => $this->str($v['year'] ?? null),
                'Mise en circulation' => $this->date($v['registrationDate'] ?? null),
                'Kilométrage' => $this->mileage($v),
                'Première main' => $this->bool($v['isFirstHand'] ?? null),
                'Historique' => $this->label(self::ORIGIN_LABELS, $v['origin'] ?? null),
                'Provenance' => $this->str($v['provenance'] ?? null),
                'Véhicule importé' => $this->bool($v['isImported'] ?? null),
                'Disponible depuis' => $this->date($advert['availabilityDate'] ?? null),
                'Référence' => $this->str($advert['reference'] ?? null),
            ]),
            $this->section('Motorisation', [
                'Énergie' => AdvertMapper::energyLabel($this->str($v['energy'] ?? null)),
                'Boîte de vitesses' => AdvertMapper::gearboxLabel($this->str($v['gearbox'] ?? null)),
                'Nombre de rapports' => $this->str($v['gears'] ?? null),
                'Transmission' => $this->str($v['transmission'] ?? null),
                'Puissance' => $this->power($v),
                'Puissance fiscale' => $this->unit($v['fiscalPower'] ?? null, 'CV'),
                'Cylindrée' => $this->unit($v['cylinder'] ?? null, 'cm³'),
            ]),
            $this->section('Consommation et environnement', [
                'Consommation mixte' => $this->unit($v['consumption'] ?? null, 'L/100 km', 1),
                'Consommation urbaine' => $this->unit($v['urbanConsumption'] ?? null, 'L/100 km', 1),
                'Consommation extra-urbaine' => $this->unit($v['extraUrbanConsumption'] ?? null, 'L/100 km', 1),
                'Émissions CO₂ (WLTP)' => $this->unit($v['emissionWLTP'] ?? null, 'g/km'),
                'Émissions CO₂ (NEDC)' => $this->unit($v['emissionCO2'] ?? null, 'g/km'),
                'Vignette Crit\'Air' => $this->critair($v['classification'] ?? null),
                'Classe énergie' => $this->str($v['ecoClass'] ?? null),
                'Malus écologique' => $this->money($v['malus'] ?? null),
            ]),
            $this->section('Électrique', [
                'Autonomie WLTP' => $this->unit($v['eAutonomyWltp'] ?? null, 'km'),
                'Capacité batterie' => $this->unit($v['eBatteryCapacity'] ?? null, 'kWh', 1),
                'Type de batterie' => $this->str($v['eBatteryType'] ?? null),
                'Garantie batterie' => $this->str($v['eBatteryWarranty'] ?? null),
                'Puissance moteur électrique' => $this->unit($v['eEnginePower'] ?? null, 'kW'),
                'Consommation électrique' => $this->unit($v['eConsumption'] ?? null, 'kWh/100 km', 1),
                'Recharge 0-100 % (prise renforcée)' => $this->str($v['eFullRechargeTimeStandard'] ?? null),
                'Recharge 0-100 % (wallbox)' => $this->str($v['eFullRechargeTimeHome'] ?? null),
                'Recharge 0-100 % (borne publique)' => $this->str($v['eFullRechargeTimePublic'] ?? null),
                'Recharge 0-100 % (superchargeur)' => $this->str($v['eFullRechargeTimeSupercharger'] ?? null),
                'Recharge 20-80 % (prise renforcée)' => $this->str($v['eIdealRechargeTimeStandard'] ?? null),
                'Recharge 20-80 % (wallbox)' => $this->str($v['eIdealRechargeTimeHome'] ?? null),
                'Recharge 20-80 % (borne publique)' => $this->str($v['eIdealRechargeTimePublic'] ?? null),
                'Recharge 20-80 % (superchargeur)' => $this->str($v['eIdealRechargeTimeSupercharger'] ?? null),
                'Précisions' => $this->str($v['eDescription'] ?? null),
            ]),
            $this->section('Couleurs et intérieur', [
                'Couleur' => $this->str($v['color'] ?? null),
                'Teinte' => $this->label(self::MAIN_COLOR_LABELS, $v['mainColor'] ?? null),
                'Couleur intérieure' => $this->str($v['insideColor'] ?? null),
                'Sellerie' => $this->label(self::INTERIOR_LABELS, $v['interior'] ?? null),
            ]),
            $this->section('Dimensions et capacités', [
                'Portes' => $this->str($v['doors'] ?? null),
                'Places' => $this->str($v['seats'] ?? null),
                'Longueur' => $this->unit($v['length'] ?? null, 'mm'),
                'Largeur' => $this->unit($v['width'] ?? null, 'mm'),
                'Hauteur' => $this->unit($v['height'] ?? null, 'mm'),
                'Empattement' => $this->unit($v['wheelbase'] ?? null, 'mm'),
                'Volume du coffre' => $this->unit($v['bootVolume'] ?? null, 'L'),
                'Réservoir' => $this->unit($v['tankVolume'] ?? null, 'L'),
                'Poids à vide' => $this->unit($v['weight'] ?? null, 'kg'),
                'Masse en ordre de marche' => $this->unit($v['mass'] ?? null, 'kg'),
                'PTAC' => $this->unit($v['gvw'] ?? null, 'kg'),
                'PTRA' => $this->unit($v['gcw'] ?? null, 'kg'),
                'Charge utile' => $this->unit($v['usefulPayload'] ?? null, 'kg'),
                'Longueur utile' => $this->unit($v['usefulLength'] ?? null, 'mm'),
                'Largeur utile' => $this->unit($v['usefulWidth'] ?? null, 'mm'),
                'Hauteur utile' => $this->unit($v['usefulHeight'] ?? null, 'mm'),
                'Hauteur du seuil de chargement' => $this->unit($v['loadSillHeight'] ?? null, 'mm'),
                'Largeur entre passages de roue' => $this->unit($v['wheelArchWidth'] ?? null, 'mm'),
            ]),
            $this->section('Garantie', [
                'Garantie' => $this->label(self::WARRANTY_LABELS, $v['warranty'] ?? null),
                'Fin de garantie constructeur' => $this->date($v['warrantyEndDate'] ?? null),
                'Nombre de clés' => $this->str($v['nbKeys'] ?? null),
            ]),
        ];

        return [
            'sections' => array_values(array_filter($sections)),
            'equipments' => $this->equipments($v['equipments'] ?? []),
            'options' => $this->options($v['options'] ?? []),
            'price_notes' => $this->priceNotes($prices),
        ];
    }

    private function section(string $title, array $items): ?array
    {
        $rows = [];
        foreach ($items as $label => $value) {
            if ($value !== null && $value !== '') {
                $rows[] = ['label' => $label, 'value' => $value];
            }
        }

        return $rows === [] ? null : ['title' => $title, 'items' => $rows];
    }

    private function equipments(mixed $equipments): array
    {
        if (! is_array($equipments)) {
            return [];
        }

        $groups = [];
        foreach ($equipments as $equipment) {
            $name = is_array($equipment) ? $this->str($equipment['name'] ?? null) : null;
            if ($name === null || ($equipment['isMissing'] ?? false) === true) {
                continue;
            }

            $category = $this->str($equipment['category'] ?? null);
            $category = isset(self::CATEGORY_LABELS[$category]) ? $category : 'OTHER';

            $groups[$category][] = ['name' => $name, 'details' => $this->details($equipment['details'] ?? [])];
        }

        $result = [];
        foreach (self::CATEGORY_LABELS as $code => $label) {
            if (isset($groups[$code])) {
                $result[] = ['category' => $label, 'items' => $groups[$code]];
            }
        }

        return $result;
    }

    private function options(mixed $options): array
    {
        if (! is_array($options)) {
            return [];
        }

        $result = [];
        foreach ($options as $option) {
            $name = is_array($option) ? $this->str($option['name'] ?? null) : null;
            if ($name === null) {
                continue;
            }

            $price = $this->number($option['price'] ?? null);

            $result[] = [
                'name' => $name,
                'price' => $price !== null && $price > 0 ? $this->money($price).' HT' : null,
                'details' => $this->details($option['details'] ?? []),
            ];
        }

        return $result;
    }

    private function priceNotes(array $prices): array
    {
        $notes = [];

        if ($label = $this->label(self::TAX_LABELS, $prices['taxCode'] ?? null)) {
            $notes[] = $label;
        }

        if (($prices['reclaimableVAT'] ?? null) === true) {
            $notes[] = 'TVA récupérable';
        }

        return $notes;
    }

    private function details(mixed $details): array
    {
        if (! is_array($details)) {
            return [];
        }

        return array_values(array_filter(array_map(fn ($d) => $this->str($d), $details)));
    }

    private function mileage(array $v): ?string
    {
        $value = $this->unit($v['mileage'] ?? null, 'km');
        if ($value === null) {
            return null;
        }

        return ($v['isMileageGuaranteed'] ?? null) === true ? $value.' (garanti)' : $value;
    }

    private function power(array $v): ?string
    {
        $ch = $this->unit($v['power'] ?? null, 'ch');
        $kw = $this->unit($v['powerKw'] ?? null, 'kW');

        return match (true) {
            $ch !== null && $kw !== null => "{$ch} ({$kw})",
            default => $ch ?? $kw,
        };
    }

    private function critair(mixed $value): ?string
    {
        $value = $this->str($value);
        if ($value === null) {
            return null;
        }

        return $value === '0' ? 'Crit\'Air 0 (électrique)' : "Crit'Air {$value}";
    }

    private function label(array $labels, mixed $code): ?string
    {
        $code = $this->str($code);
        if ($code === null || $code === 'UNKNOWN') {
            return null;
        }

        return $labels[$code] ?? $code;
    }

    private function unit(mixed $value, string $unit, int $decimals = 0): ?string
    {
        $number = $this->number($value);
        if ($number === null || $number <= 0) {
            return null;
        }

        return $this->format($number, $decimals).self::NBSP.$unit;
    }

    private function money(mixed $value): ?string
    {
        $number = $this->number($value);
        if ($number === null || $number <= 0) {
            return null;
        }

        return $this->format($number, 0).self::NBSP.'€';
    }

    private function format(float $number, int $decimals): string
    {
        $formatted = number_format($number, $decimals, ',', self::NBSP);

        return $decimals > 0 ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
    }

    private function number(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $normalized = str_replace([' ', self::NBSP, ','], ['', '', '.'], trim($value));

            return is_numeric($normalized) ? (float) $normalized : null;
        }

        return null;
    }

    private function date(mixed $value): ?string
    {
        $value = $this->str($value);
        if ($value === null || ! preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $value, $m)) {
            return null;
        }

        return "{$m[3]}/{$m[2]}/{$m[1]}";
    }

    private function bool(mixed $value): ?string
    {
        return is_bool($value) ? ($value ? 'Oui' : 'Non') : null;
    }

    private function str(mixed $value): ?string
    {
        if (is_bool($value) || ! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
