<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait HasCodeName
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function bootHasCodeName()
    {
        static::creating(function ($model) {
            $model->generateCodeName();
        });

        static::updating(function ($model) {
            if ($model->isDirty(['name', 'city', 'type'])) {
                $model->generateCodeName();
            }
        });
    }

    public function generateCodeName(): void
    {
        $value = $this->name.' '.($this->city ?? $this->type);
        // only want alphabets and spaces
        $code = Str::replaceMatches('/[^A-Z a-z]/', '', $value);
        $candidate = Str::kebab($code);

        // Check for uniqueness
        $query = static::where('code_name', $candidate);

        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        if ($query->exists()) {
            $errorField = 'city'; // Default blame

            // If name changed but city didn't, blame name
            if ($this->isDirty('name') && ! $this->isDirty('city')) {
                $errorField = 'name';
            }

            // If the model doesn't have a city field (e.g. Diocese), blame name
            if (! isset($this->city) && $this->getAttribute('city') === null) {
                $errorField = 'name';
            }

            throw ValidationException::withMessages([
                $errorField => ["The code name '{$candidate}' is already in use by another record."],
            ]);
        }

        $this->code_name = $candidate;
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $query = $this->where('code_name', $value);

        if (is_numeric($value)) {
            $query->orWhere('id', $value);
        }

        return $query->firstOrFail();
    }
}
