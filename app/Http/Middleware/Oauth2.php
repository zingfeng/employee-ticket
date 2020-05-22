<?php

namespace App\Http\Middleware;

use Closure;
use \Firebase\JWT\JWT;

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
        Pre-Middleware Action
        $jwt = substr($request->header('authorization'), 7);
        // $jwt = $request->header('accesstoken');
        if (!$jwt) {
            return response()->json(["error" => 'access_denined', 'error_description'=>'Không được phép truy cập. Không nhận được authorization']);
        }
        // Pre-Middleware Action
        $public_key = "-----BEGIN PUBLIC KEY-----\n".env('KEYCLOAK_JWT_PUBLIC_KEY')."\n-----END PUBLIC KEY-----";

        try {
            $profile = (array)JWT::decode($jwt, $public_key , array('RS256'));
        } catch(\Firebase\JWT\ExpiredException $e){
            return response()->json(['error' => 'access_token_invalid','error_description' => $e->getMessage()]);
        }
        if(!isset($profile['tracking_setting_id'])) {
            $profile['tracking_setting_id'] = 1;
        }
        $request->merge( ['employee_id' => $profile['user_id'],'profile' => (array)$profile ]);
        $response = $next($request);

        // $request->merge( ['employee_id' => 12,'profile' => ['department_id' => 4,'tracking_setting_id'=> 1] ] );
        // $response = $next($request);

        // Post-Middleware Action

        return $response;
    }
}
