<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

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
            $value = $model->name.' '.($model->city ?? $model->type);
            // only want alphabets and spaces
            $code = Str::replaceMatches('/[^A-Z a-z]/', '', $value);
            $model->code_name = Str::kebab($code);
        });
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('id', $value)->orWhere('code_name', $value)->firstOrFail();
    }
}
