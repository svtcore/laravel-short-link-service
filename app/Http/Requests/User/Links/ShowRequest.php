<?php

namespace App\Http\Requests\User\Links;

use Illuminate\Foundation\Http\FormRequest;

class ShowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('user');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => [
                'required', 
                'integer', 
                'min:1', 
                'exists:links,id'
            ],
            'startDate' => [
                'nullable',
                'date',
                'before_or_equal:endDate',
                'before_or_equal:today',
            ],
            'endDate' => [
                'nullable',
                'date',
                'after_or_equal:startDate',
                'before_or_equal:today',
            ],
        ];
    }
}
