<?php

namespace App\Http\Requests\Admin\Domains;

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
            'domainName' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^(?!:\/\/)([a-zA-Z0-9-_]+\.)*[a-zA-Z]{2,}$/', // check without http/https
                'unique:domains,name',
            ],
            'domainStatus' => [
                'required',
                'boolean',
            ],
        ];
    }
}
