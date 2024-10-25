<?php

namespace App\Rules;

use Closure;
use App\Models\Parish;
use App\Models\SubParish;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateParishableId implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

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

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string = null): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $type = Arr::get($this->data, 'type')) {
            $fail('Type not defined');
        }

        if ($type === 'sub-parish' && SubParish::whereId($value)->exists()) {
            return;
        }

        if ($type !== 'sub-parish' && Parish::whereId($value)->exists()) {
            return;
        }

        $fail('The parish id is invalid');
    }
}
