<?php

namespace App\Http\Controllers;

use App\Http\Concerns\PaginatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use PaginatesRequests;
    use ValidatesRequests;
}
