<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;

class SystemController extends Controller
{
    public function refreshCsrf()
    {
        return csrf_token();
    }
}
