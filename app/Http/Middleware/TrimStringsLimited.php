<?php

namespace App\Http\Middleware;

class TrimStringsLimited extends TrimStrings
{
    protected $except = ['search_untrimmed'];
}
