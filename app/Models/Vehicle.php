<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'title',
        'brand',
        'model',
        'version',
        'offer_type',
        'energy',
        'gearbox',
        'mileage',
        'year',
        'power',
        'color',
        'price',
        'catalog_price',
        'main_picture_url',
        'pictures',
        'url',
        'raw',
        'synced_at',
    ];

    protected $hidden = ['raw'];

    protected function casts(): array
    {
        return [
            'mileage' => 'integer',
            'year' => 'integer',
            'power' => 'integer',
            'price' => 'float',
            'catalog_price' => 'float',
            'pictures' => 'array',
            'raw' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function scopeOfBrand(Builder $query, ?string $brand): Builder
    {
        $brand = $brand !== null ? trim($brand) : '';

        return $query->when(
            $brand !== '',
            fn (Builder $q) => $q->whereRaw('LOWER(brand) = ?', [mb_strtolower($brand)]),
        );
    }
}
