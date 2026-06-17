<?php

namespace App\Http\Requests\TimeSpent;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TimeSpentUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid'],
            'user_time_spent' => ['required', 'array', 'min:1'],
            'user_time_spent.*.url' => ['required', 'string'],
            'user_time_spent.*.timestamp' => ['required', 'date'],
        ];
    }

    /**
     * Customiza as mensagens de erro (Opcional, mas ajuda no debug da extensão)
     */
    public function messages(): array
    {
        return [
            'user_time_spent.*.url.required' => 'A URL de cada registro é obrigatória.',
            'user_time_spent.*.timestamp.required' => 'O timestamp de cada registro é obrigatório.',
            'user_time_spent.*.timestamp.date' => 'O formato do timestamp precisa ser uma data válida.',
        ];
    }
}