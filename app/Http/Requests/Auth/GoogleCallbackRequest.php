<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class GoogleCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'  => ['required', 'string'],
            'state' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code'  => 'código de autorização',
            'state' => 'estado CSRF',
        ];
    }
}
