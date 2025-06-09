<?php

namespace App\Http\Requests\Admin\Search;

use Illuminate\Foundation\Http\FormRequest;

class LinkByIPRequest extends FormRequest
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
            'ip' => [
                'required',
                'string',
                'regex:/^('
                    . '(\d{1,3}\.){3}\d{1,3}|'          // IPv4
                    . '([a-f0-9]{1,4}:){7}[a-f0-9]{1,4}|' // full IPv6
                    . '(::[a-f0-9]{1,4})|'              // short IPv6
                    . '([a-f0-9]{1,4}::[a-f0-9]{1,4})'   // mixed IPv6
                . ')$/i'
            ],
        ];
    }
}
