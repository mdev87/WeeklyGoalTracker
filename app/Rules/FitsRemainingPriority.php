<?php

namespace App\Rules;

use App\Models\Goal;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Translation\PotentiallyTranslatedString;

class FitsRemainingPriority implements ValidationRule
{
    public function __construct(
        protected ?Goal $goal = null
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var int */
        $total = Goal::whereUserId(Auth::id())->sum('priority_percentage');

        if ($this->goal) {
            $total -= $this->goal->priority_percentage;
        }

        $remaining = max(100 - $total, 0);

        if ($value < 0 || $value > $remaining) {
            $fail("The :attribute may not be greater than the remaining {$remaining}% priority.");
        }
    }
}
