<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
{
    public function toArray($request): array
    {
        // احسب التقييم من متوسط الطلبات — أو ثابت مؤقتاً
        $rating = $this->calculateRating();

        return [
            'id'             => $this->id,
            'type'           => $this->type,
            'orders_count'   => $this->orders_count ?? 0,
            'rating'         => $rating,
            'delivery_mins'  => 15,   // يمكن جعله ديناميكياً بعدين
            'is_available'   => true, // يمكن إضافة حقل للـ providers بغدين
            'user'           => [
                'id'         => $this->user?->id,
                'first_name' => $this->user?->first_name,
                'last_name'  => $this->user?->last_name,
                'phone'      => $this->user?->phone,
                'address'    => $this->user?->address,
                'image_path' => $this->buildImageUrl(),
            ],
            'products_count' => $this->products->count(),
            'created_at'     => $this->created_at,
        ];
    }

    //  helpers

    private function buildImageUrl(): ?string
    {
        $path = $this->user?->image_path;
        if (!$path) return null;
        if (str_starts_with($path, 'http')) return $path;
        return asset('storage/' . str_replace('\\', '/', $path));
    }

    private function calculateRating(): float
    {

        $base = 4.5;
        $bonus = ($this->id % 5) * 0.1;
        return round($base + $bonus, 1);
    }
}
