<?php

namespace App\Http\Requests\Admin\Domains;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{

    public function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
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
            'id' => ['required', 'numeric', 'min:1', 'max:999999999', 'exists:domains,id'],
            'domainName' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^(?!:\/\/)([a-zA-Z0-9-_]+\.)*[a-zA-Z]{2,}$/', // check without http/https
            ],
            'domainStatus' => [
                'required',
                'boolean',
            ],
        ];
    }
}
