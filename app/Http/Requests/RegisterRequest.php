<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'first_name'    => 'required|string|max:100',
            'second_name'   => 'nullable|string|max:100',
            'last_name'     => 'required|string|max:100',
            'email'         => 'sometimes|required|email|unique:users,email,' . ($userId ?? 'NULL'),
            'password'      => 'sometimes|nullable|string|min:6',
            'phone'         => 'nullable|string|min:10|max:20',
            'role'          => 'sometimes|string|exists:roles,name',
            'gender'        => 'nullable|in:0,1',
            'address'       => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
        ];
    }
}
