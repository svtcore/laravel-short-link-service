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
            'url' =>  [
                'required',
                'string',
                'max:2048',
                'regex:/^https?:\/\/[^\s"\'<>{}|\\^`\[\]\\\\]+$/i',
            ],
            'custom_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_\- ]+$/'],
            'from_modal' => ['nullable', 'boolean']
        ];        
    }
}
