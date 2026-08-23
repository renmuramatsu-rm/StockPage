<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('themes', 'name')->ignore($this->route('theme')),
            ],
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
        ];
    }
}
