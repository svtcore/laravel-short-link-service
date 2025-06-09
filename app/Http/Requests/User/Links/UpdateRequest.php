<?php

namespace App\Http\Requests\User\Links;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('user');
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1', 'exists:links,id'],
            'custom_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_\- ]+$/'],
            'destination' => [
                'required',
                'url',
                'max:2048',
            ],
            'access' => ['required', 'boolean'],
        ];
    }
}
