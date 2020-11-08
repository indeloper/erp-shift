<?php

namespace App\Http\Middleware;

use Closure;

class TrimStringsLimited extends TrimStrings
{

    protected $except = ['search_untrimmed'];

}
