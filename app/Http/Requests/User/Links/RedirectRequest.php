<?php

namespace App\Http\Requests\User\Links;

use Illuminate\Foundation\Http\FormRequest;

class RedirectRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'host' => [
                'required',
                'string',
                'max:255',
            ],
            'path' => [
                'nullable',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-zA-Z0-9]+$/',
            ],
            'user_agent' => [
                'required',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-_\/\.:;=()+*,]+$/',
            ],
            'ip' => [
                'required',
                'ip',
            ],
        ];
    }

    public function validationData(): array
    {
        return array_merge($this->all(), [
            'host' => $this->getHost(),
            'path' => ltrim($this->path(), '/'),
            'user_agent' => $this->userAgent(),
            'ip' => $this->ip(),
        ]);
    }
}
