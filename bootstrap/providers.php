<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\DatabaseServiceProvider;

return [
    AppServiceProvider::class,
    DatabaseServiceProvider::class,
    FortifyServiceProvider::class,
];
