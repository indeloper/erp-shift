<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class LogRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /*Log::info('Incoming Request:', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'parameters' => $request->all(),
        ]);*/

        return $next($request);
    }
}
