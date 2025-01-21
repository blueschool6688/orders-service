<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IngredientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
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
            'name' => 'required|string|min:1',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|min:1',
            'max_quantity' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($value !== null && $value >= $this->quantity) {
                        $fail(__('validation.max_quantity_less_than_quantity'));
                    }
                },
            ],
        ];
    }
}
