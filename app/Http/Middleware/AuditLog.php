<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use App\Audit_log;

class AuditLog
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
        return $next($request);
    }

    public function terminate($request, $response)
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode == 404) {
            return;
        }

        $user = JWTAuth::parseToken()->authenticate()->getAttributes();
        $method = $request->getMethod();
        $requestUri = str_replace('/api/', '', $request->getRequestUri());

        if ($method == 'POST') {
            $response = json_decode($response->getContent())->response;
            Audit_log::create([
                'user_id' => $user['id'],
                'module' => $requestUri,
                'event' => 'CREACIÓN',
                'record_id' => $response->id
            ]);
        }

        if ($method == 'PUT' || $method == 'PATCH') {
            $requestUri = explode('/', $requestUri);
            Audit_log::create([
                'user_id' => $user['id'],
                'module' => $requestUri[0],
                'event' => 'ACTUALIZACIÓN',
                'record_id' => (int) $requestUri[1]
            ]);
        }

        if ($method == 'DELETE') {
            $requestUri = explode('/', $requestUri);
            Audit_log::create([
                'user_id' => $user['id'],
                'module' => $requestUri[0],
                'event' => 'ELIMINACIÓN',
                'record_id' => (int) $requestUri[1]
            ]);
        }
    }
}
