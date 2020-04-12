<?php

namespace App\Http\Middleware;

use Closure;

class Oauth2
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
        // Pre-Middleware Action
        $request->merge( ['employee_id' => 201,'profile' => ['department_id' => 4,'tracking_setting_id'=> 1] ] );
        $response = $next($request);

        // Post-Middleware Action

        return $response;
    }
}
