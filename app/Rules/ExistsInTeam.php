<?php

namespace App\Rules;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates that a model exists within the current team's scope.
 *
 * This rule uses Eloquent to check existence, which automatically applies
 * the TeamScope global scope for models that use the BelongsToTeam trait.
 * This ensures users cannot reference records from other teams.
 */
class ExistsInTeam implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @param  class-string<Model>  $modelClass  The fully qualified model class name
     * @param  string  $column  The column to check against (default: 'id')
     */
    public function __construct(
        protected string $modelClass,
        protected string $column = 'id'
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        // Using Eloquent query applies the TeamScope automatically
        // for models that extend BaseModel (which uses BelongsToTeam trait)
        $exists = $this->modelClass::query()
            ->where($this->column, $value)
            ->exists();

        if (! $exists) {
            $fail('The selected :attribute is invalid.');
        }
    }
}
