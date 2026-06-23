<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->user?->first_name . ' ' . $this->user?->last_name,
            'is_available' => (bool) $this->is_available,
           // 'rating'       => $this->rating||4.8,
            'rating'       => 4.8,
            'image'        => $this->user?->image_path
                ? asset('storage/' . $this->user->image_path)
                : null,
        ];
    }
}
