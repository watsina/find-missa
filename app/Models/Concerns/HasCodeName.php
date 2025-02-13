<?php

namespace App\Models\Concerns;

trait HasCodeName
{
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('id', $value)->orWhere('code_name', $value)->firstOrFail();
    }
}
