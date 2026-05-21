<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'provider_id' => $this->provider_id,
            'type'        => $this->type,
            'description' => $this->description,
            'price'       => (float) $this->price,
           'image_path' => $this->image_path,
            'image_url' => $this->image_path
            ? asset('storage/' . str_replace('\\', '/', $this->image_path))
    :        null,
            'created_at'  => $this->created_at,
        ];
    }
}
