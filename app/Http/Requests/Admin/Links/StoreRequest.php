<?php

namespace App\Http\Requests\Admin\Links;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'url' => ['required', 'url', 'max:2048'],
            'custom_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_\- ]+$/'],
            'user_email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
