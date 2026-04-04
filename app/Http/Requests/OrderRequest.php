<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'              => 'required|exists:users,id',
            'provider_id'          => 'required|exists:providers,id',
            'driver_id'            => 'required|exists:drivers,id',
            'order_address'        => 'required|string|max:255',
            'products'             => 'required|array|min:1',
            'products.*.product_id'=> 'required|exists:products,id',
            'products.*.quantity'  => 'required|integer|min:1',
        ];
    }
}
