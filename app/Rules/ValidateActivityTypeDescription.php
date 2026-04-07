<?php

namespace App\Rules;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Enums\ActivityTypeDescription;
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
        // Derive the sibling "type" field path from the current attribute.
        // For nested attributes like "times.0.activity_type_description", the sibling is "times.0.type".
        // For top-level "activity_type_description", the sibling is "type".
        $typePath = Str::beforeLast($attribute, '.') === $attribute
            ? 'type'
            : Str::beforeLast($attribute, '.').'.type';

        if (! $type = Arr::get($this->data, $typePath)) {
            $fail('Activity type description requires the activity type');

            return;
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
