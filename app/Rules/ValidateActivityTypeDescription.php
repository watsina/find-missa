<?php

namespace App\Rules;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateActivityTypeDescription implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $type = Arr::get($this->data, 'type')) {
            $fail('Activity type description requires the activity type');
        }

        $types = [$type];
        // NOTE: the service is to cater for good-friday-service which we want to show under masses
        if ($type === 'mass') {
            $types[] = 'service';
        }
        if (! Str::contains($value, $types)) {
            $fail('Invalid activity type description');
        }
    }

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
