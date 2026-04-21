<?php

namespace App\Http\Concerns;

use Illuminate\Http\Request;

trait PaginatesRequests
{
    /**
     * Get the sanitized items-per-page value from the request.
     */
    protected function getItemsPerPage(Request $request, int $default = 20, int $max = 100): int
    {
        $items = abs(intval($request->itemsPerPage)) ?: $default;

        return min($items, $max);
    }
}
