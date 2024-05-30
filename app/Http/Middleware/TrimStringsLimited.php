<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings;

class TrimStringsLimited extends TrimStrings
{
    protected $except = ['search_untrimmed'];
}
