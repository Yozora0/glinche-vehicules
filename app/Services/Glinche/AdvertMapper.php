<?php

namespace App\Services\Glinche;

class AdvertMapper
{
    public const ENERGY_LABELS = [
        'ES' => 'Essence',
        'GO' => 'Diesel',
        'EL' => 'Électrique',
        'EH' => 'Hybride essence',
        'GH' => 'Hybride diesel',
        'EE' => 'Hybride rechargeable essence',
        'GL' => 'Hybride rechargeable diesel',
        'NH' => 'Hybride GNV',
        'NE' => 'Hybride rechargeable GNV',
        'EG' => 'Essence / GPL',
        'EN' => 'Essence / GNV',
        'FG' => 'Essence / Bioéthanol',
        'ET' => 'Éthanol',
        'H2' => 'Hydrogène',
        'AC' => 'Air comprimé',
    ];

    public const GEARBOX_LABELS = [
        'AUT' => 'Automatique',
        'MAN' => 'Manuelle',
        'NOG' => 'Sans boîte',
        'PDK' => 'PDK',
        'ROB' => 'Robotisée',
        'SEM' => 'Semi-automatique',
        'SEQ' => 'Séquentielle',
    ];

    public const OFFER_TYPE_LABELS = [
        'VN' => 'Neuf',
        'VO' => 'Occasion',
        '0KM' => '0 km',
    ];

    public function map(array $advert): ?array
    {
        $vehicle = is_array($advert['vehicle'] ?? null) ? $advert['vehicle'] : [];
        $prices = is_array($vehicle['prices'] ?? null) ? $vehicle['prices'] : [];

        $reference = $this->string($advert['reference'] ?? null);
        $brand = $this->brand($vehicle['manufacturer'] ?? null);

        if ($reference === null || $brand === null) {
            return null;
        }

        $pictures = $this->pictures($advert['pictures'] ?? []);

        return [
            'reference' => $reference,
            'title' => $this->string($advert['title'] ?? null),
            'brand' => $brand,
            'model' => $this->model($vehicle['model'] ?? null),
            'version' => $this->string($vehicle['finish'] ?? null),
            'offer_type' => $this->string($vehicle['type'] ?? null),
            'energy' => $this->string($vehicle['energy'] ?? null),
            'gearbox' => $this->string($vehicle['gearbox'] ?? null),
            'mileage' => $this->int($vehicle['mileage'] ?? null),
            'year' => $this->int($vehicle['year'] ?? null) ?? $this->yearFromDate($vehicle['registrationDate'] ?? null),
            'power' => $this->int($vehicle['power'] ?? null),
            'color' => $this->string($vehicle['color'] ?? null),
            'price' => $this->money($prices['price'] ?? null) ?? $this->money($prices['merchantPrice'] ?? null),
            'catalog_price' => $this->money($prices['catalogPrice'] ?? null),
            'main_picture_url' => $pictures['main'],
            'pictures' => $pictures['all'],
            'url' => $this->string($vehicle['url'] ?? null),
            'raw' => $advert,
            'is_deleted' => $this->string($advert['deleteDate'] ?? null) !== null,
        ];
    }

    public static function energyLabel(?string $code): ?string
    {
        return $code === null ? null : (self::ENERGY_LABELS[$code] ?? $code);
    }

    public static function gearboxLabel(?string $code): ?string
    {
        return $code === null ? null : (self::GEARBOX_LABELS[$code] ?? $code);
    }

    public static function offerTypeLabel(?string $code): ?string
    {
        return $code === null ? null : (self::OFFER_TYPE_LABELS[$code] ?? $code);
    }

    private const BRAND_ACRONYMS = ['BMW', 'DS', 'MG', 'BYD', 'KGM', 'VW', 'GMC', 'BAIC', 'GWM', 'XEV'];

    private function brand(mixed $value): ?string
    {
        $brand = $this->string($value);
        if ($brand === null) {
            return null;
        }

        $brand = preg_replace('/\s+/u', ' ', $brand);

        if (in_array(mb_strtoupper($brand), self::BRAND_ACRONYMS, true)) {
            return mb_strtoupper($brand);
        }

        return mb_convert_case(mb_strtolower($brand), MB_CASE_TITLE);
    }

    private function model(mixed $value): ?string
    {
        $model = $this->string($value);
        if ($model === null) {
            return null;
        }

        $model = preg_replace('/\s+/u', ' ', $model);

        if (preg_match('/\d/', $model) || $model !== mb_strtoupper($model)) {
            return $model;
        }

        return mb_convert_case(mb_strtolower($model), MB_CASE_TITLE);
    }

    private function pictures(mixed $pictures): array
    {
        if (! is_array($pictures)) {
            return ['main' => null, 'all' => []];
        }

        $main = null;
        $all = [];

        foreach ($pictures as $picture) {
            $url = is_array($picture) ? $this->string($picture['url'] ?? null) : null;
            if ($url === null) {
                continue;
            }

            if ($main === null && ($picture['type'] ?? null) === 'MAIN') {
                $main = $url;
            }

            $all[] = $url;
        }

        return ['main' => $main ?? ($all[0] ?? null), 'all' => $all];
    }

    private function string(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function int(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && is_numeric(trim($value))) {
            return (int) round((float) $value);
        }

        if (is_float($value)) {
            return (int) round($value);
        }

        return null;
    }

    private function money(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $normalized = str_replace([' ', "\u{00A0}", ','], ['', '', '.'], trim($value));

            return is_numeric($normalized) ? (float) $normalized : null;
        }

        return null;
    }

    private function yearFromDate(mixed $value): ?int
    {
        $date = $this->string($value);
        if ($date === null || ! preg_match('/^(\d{4})-/', $date, $m)) {
            return null;
        }

        return (int) $m[1];
    }
}
