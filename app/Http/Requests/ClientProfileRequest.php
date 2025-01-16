<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientProfileRequest extends FormRequest
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
    public function rules()
    {
        return [
            'first_name'   => ['required', 'string', 'max:190'],
            'last_name'    => ['required', 'string', 'max:190'],
            'email'        => [
                'nullable',
                'email',
                'max:190',
                Rule::unique("users", "email")->ignore(auth('sanctum-client')->user()->id)
            ],
            'phone'        => ['nullable', 'string', 'max:20', Rule::unique("users", "phone")->ignore(auth('sanctum-client')->user()->id)],
            'country_code' => ['required', 'string', 'max:20'],
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->filled('email') && !$this->filled('phone')) {
                $validator->errors()->add('email', __('At least one of email or phone must be provided.'));
                $validator->errors()->add('phone', __('At least one of email or phone must be provided.'));
            }
        });
    }
}
