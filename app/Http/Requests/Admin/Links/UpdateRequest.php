<?php

namespace App\Http\Requests\Admin\Links;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
            'url' => $this->input('editURL'),
            'custom_name' => $this->input('editCustomName'),
            'status' => $this->input('editStatus'),
        ]);
    }
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
            'id' => ['required', 'numeric', 'min:1', 'max:999999999', 'exists:links,id'],
            'custom_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_\- ]+$/'],
            'url' => ['required', 'string', 'url', 'max:2048'],
            'status' => ['nullable', 'boolean']
        ];
    }
}
