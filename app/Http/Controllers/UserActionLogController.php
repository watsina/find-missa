<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserActionLog;

class UserActionLogController extends Controller
{
    public function __invoke(Request $request)
    {
        return UserActionLog::with('user:id,name,profile_photo_path')->latest('occurred_at')->cursorPaginate(20);
    }
}
