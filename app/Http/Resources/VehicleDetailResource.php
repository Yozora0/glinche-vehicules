<?php

namespace App\Http\Resources;

use App\Services\Glinche\VehicleDetailPresenter;
use Illuminate\Http\Request;

class VehicleDetailResource extends VehicleResource
{
    public function toArray(Request $request): array
    {
        $details = (new VehicleDetailPresenter)->present($this->raw ?? []);

        return [
            ...parent::toArray($request),
            'pictures' => $this->orderedPictures(),
            ...$details,
        ];
    }

    private function orderedPictures(): array
    {
        $pictures = $this->pictures ?? [];
        $main = $this->main_picture_url;

        if ($main === null) {
            return $pictures;
        }

        return [$main, ...array_values(array_filter($pictures, fn ($url) => $url !== $main))];
    }
}
