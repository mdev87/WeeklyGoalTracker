<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeLogRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'duration_minutes' => 'sometimes|required|integer|min:1',
            'date' => 'sometimes|required|date_format:Y-m-d|before_or_equal:'.jdate()->format('Y-m-d'),
            'note' => 'nullable|string|max:65535',
            'goal_id' => "sometimes|required|exists:App\Models\Goal,id",
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.before_or_equal' => 'The date field must not be the future date',
        ];
    }
}
