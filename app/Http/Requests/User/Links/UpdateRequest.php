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
            'id' => ['required', 'numeric', 'min:1', 'max:999999999', 'exists:links,id'],
            'custom_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_\- ]+$/'],
            'destination' => [
                'required',
                'string',
                'max:2048',
                'regex:/^https?:\/\/[^\s"\'<>{}|\\^`\[\]\\\\]+$/i',
            ],
            'access' => ['required', 'boolean'],
        ];
    }
}
