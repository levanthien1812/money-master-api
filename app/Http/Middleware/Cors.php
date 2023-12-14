<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add CORS headers
        $response->headers->set('Access-Control-Allow-Origin', env('APP_FE_URL'));
        $response->headers->set('Access-Control-Allow-Methods', ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS']);

        $response->headers->set('Access-Control-Allow-Headers', ['X-CSRF-Token', 'Authorization', 'Content-Type']);
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
