<?php

namespace App\Rules;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Enums\ActivityTypeDescription;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

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
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $type = Arr::get($this->data, 'type')) {
            $fail('Activity type description requires the activity type');
        }

        // get the valid descriptions for the given type
        $validDescriptions = ActivityTypeDescription::getValidDescriptionsForType($type);
        if (! Str::contains($value, $validDescriptions)) {
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
