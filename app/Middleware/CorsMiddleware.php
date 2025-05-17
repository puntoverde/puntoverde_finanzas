<?php

namespace App\Middleware;

use Closure;
use App\Util\Auth;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers','Content-Type, Authorization, X-XSRF-TOKEN');
        // ->header("Content-type"       , "text/csv")
        // ->header("Content-Disposition", "attachment; filename=lwo.xlsx")
        // ->header("Pragma"             , "no-cache")
        // ->header("Cache-Control"      , "must-revalidate, post-check=0, pre-check=0")
        // ->header("Expires"            , "0");
    }
}
