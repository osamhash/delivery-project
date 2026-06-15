<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'user_id'        => $this->user_id,
            'provider_id'    => $this->provider_id,
            'driver_id'      => $this->driver_id,
            'status'         => $this->status?->name,
            'total_price'    => (float) $this->total_price,
            'payment_method' => $this->payment_method,
            'order_address'  => $this->order_address,
            'products'       => $this->products->map(fn($p) => [
                'id'          => $p->id,
                'type'        => $p->type,
                'description' => $p->description,
                'price'       => (float) $p->pivot->price,
                'quantity'    => $p->pivot->quantity,
                'subtotal'    => $p->pivot->price * $p->pivot->quantity,
            ]),
            'driver'         => [
                'id'   => $this->driver?->id,
                'name' => $this->driver?->user?->first_name . ' ' . $this->driver?->user?->last_name,
            ],
            'created_at'     => $this->created_at,
        ];
    }
}
