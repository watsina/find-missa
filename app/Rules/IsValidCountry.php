<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidCountry implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $countries = collect(config('countries'))->pluck('name')->toLower()->toArray();

        if (in_array(strtolower($value), $countries)) {
            return;
        }

        $fail('The :attribute must be a valid country.');
    }
}
