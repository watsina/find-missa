<?php

namespace App\Features;

use App\Models\Team;

class DataImportExport
{
    /**
     * Resolve the feature's initial value.
     * Disabled by default - must be explicitly enabled per-team.
     */
    public function resolve(Team $scope): bool
    {
        return false;
    }
}
