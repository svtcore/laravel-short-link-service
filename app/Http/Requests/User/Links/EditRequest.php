<?php

namespace App\Http\Requests\User\Links;

use Illuminate\Foundation\Http\FormRequest;

class EditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Ensure the user is authenticated and has the 'user' role
        return $this->user() && $this->user()->hasRole('user');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Since 'id' is a route parameter, we don't need to validate it as a form field
        // It can be validated in the controller or using a custom rule for route parameters
        return [];
    }

    /**
     * Custom validation for the ID parameter from the route.
     *
     * @return void
     */
    public function withValidator($validator)
    {
        // Get the 'id' directly from the route
        $id = $this->route('id');

        // Manually add a custom validation rule for the 'id' parameter
        $validator->after(function ($validator) use ($id) {
            if (!is_numeric($id) || !\App\Models\Link::find($id)) {
                $validator->errors()->add('id', 'The specified link does not exist or is invalid.');
            }
        });
    }
}
