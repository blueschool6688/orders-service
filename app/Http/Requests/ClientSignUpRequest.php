<?php

namespace App\Http\Requests;

use App\Enums\Ask;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientSignUpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name'   => ['required', 'string', 'max:255'],
            'last_name'    => ['required', 'string', 'max:255'],
            'email'        => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique("users", "email")->whereNull('deleted_at')->where('is_guest', Ask::NO)
            ],
            'phone'        => [
                'nullable',
                'numeric',
                'digits_between:8,15',
                Rule::unique("users", "phone")->whereNull('deleted_at')->where('is_guest', Ask::NO)
            ],
            'country_code' => ['required_with:phone', 'numeric'],
            'password'     => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }

    /**
     * Add custom validation rules to enforce additional constraints.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->filled('email') && !$this->filled('phone')) {
                $validator->errors()->add('login', __('Either email or phone must be provided.'));
            }
        });
    }
}
