<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
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
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if($user->inactive){
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive'
                ], 403);
            }
            
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json([
                    'success' => false,
                    'message' => 'Token is Invalid'
                ], 401);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json([
                    'success' => false,
                    'message' => 'Token is Expired'
                ], 401);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization Token not found'
                ], 401);
            }
        }
        return $next($request);
    }
}