<?php

namespace App\Http\Resources;

use App\Services\Glinche\AdvertMapper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'title' => $this->title,
            'brand' => $this->brand,
            'model' => $this->model,
            'version' => $this->version,
            'offer_type' => $this->offer_type,
            'offer_type_label' => AdvertMapper::offerTypeLabel($this->offer_type),
            'energy' => $this->energy,
            'energy_label' => AdvertMapper::energyLabel($this->energy),
            'gearbox_label' => AdvertMapper::gearboxLabel($this->gearbox),
            'mileage' => $this->mileage,
            'year' => $this->year,
            'power' => $this->power,
            'price' => $this->price,
            'catalog_price' => $this->catalog_price,
            'picture' => $this->main_picture_url,
            'pictures_count' => count($this->pictures ?? []),
            'url' => $this->url,
        ];
    }
}
