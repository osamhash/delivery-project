<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'   => 'required|string|max:100',
            'second_name'  => 'nullable|string|max:100',
            'last_name'    => 'required|string|max:100',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:6',
            'phone'        => 'nullable|string|min:10|max:20|unique:users,phone',
            'gender'       => 'nullable|in:0,1',
            'address'      => 'nullable|string|max:100',
            'is_available' => 'nullable|boolean',
        ];
    }
}
