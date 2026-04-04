<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'   => 'required|exists:users,id',
            'order_id'  => 'required|exists:orders,id',
            'driver_id' => 'required|exists:drivers,id',
            'method'    => 'required|in:cash,card',
        ];
    }
}
