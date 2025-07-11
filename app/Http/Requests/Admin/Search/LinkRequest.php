<?php

namespace App\Http\Requests\Admin\Search;

use Illuminate\Foundation\Http\FormRequest;

class LinkRequest extends FormRequest
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
            'query' => [
                'required',
                'string',
                'max:2048',
                'regex:/^[a-z0-9\-._~:\/?#\[\]@!$&\'()*+,;=]*$/i',
            ],
        ];
    }
}
