<?php

namespace App\Http\Requests\User\Links;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'url', 'max:2048'],
            'custom_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_\- ]+$/'],
            'from_modal' => ['nullable', 'boolean'],
        ];
    }
}
