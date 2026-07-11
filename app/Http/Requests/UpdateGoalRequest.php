<?php

namespace App\Http\Requests;

use App\Enums\GoalColor;
use App\Rules\FitsRemainingPriority;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGoalRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'color' => ['bail', 'sometimes', 'required', 'string', Rule::enum(GoalColor::class)],
            'priority_percentage' => [
                'bail',
                'sometimes',
                'required',
                'integer',
                new FitsRemainingPriority,
            ],
        ];
    }
}
