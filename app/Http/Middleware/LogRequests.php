<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Log;

class LogRequests
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /*Log::info('Incoming Request:', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'parameters' => $request->all(),
        ]);*/

        return $next($request);
    }
}
