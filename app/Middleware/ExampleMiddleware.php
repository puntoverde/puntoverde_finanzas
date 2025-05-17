<?php

namespace App\Middleware;

use Closure;
use App\Util\Auth;

class ExampleMiddleware
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
        $token = $request->header('Authorization');

        try{
            Auth::Check($token);
            $data=Auth::GetData($token);  
            $request->attributes->add(['token'=>$data]);
            return $next($request);
        }
        catch(\Exception $ex){
            return response('Unauthorized',401);
        }
    }
}
