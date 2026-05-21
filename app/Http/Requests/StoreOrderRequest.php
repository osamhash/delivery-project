<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id'        => 'required|exists:users,id',
            'provider_id'    => 'required|exists:providers,id',
            'driver_id'      => 'required|exists:drivers,id',
            'total_price'    => 'required|numeric|min:0',
            'payment_status' => 'required|string|in:cash,card,apple_pay,stc_pay',
            'order_address'  => 'required|string|max:100',

            'products'              => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity'   => 'required|integer|min:1',
            'products.*.price'      => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'products.required'              => 'يجب إضافة منتج واحد على الأقل.',
            'products.*.product_id.exists'   => 'المنتج غير موجود.',
            'products.*.quantity.min'        => 'الكمية يجب أن تكون 1 على الأقل.',
        ];
    }
}
