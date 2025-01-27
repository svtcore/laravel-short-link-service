<?php

namespace App\Http\Requests\User\Links;

use App\Rules\SafePHP;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SafeXSS;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'string', 'url', 'max:2048'],
            'custom_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_- ]+$/'],
        ];
    }
}
