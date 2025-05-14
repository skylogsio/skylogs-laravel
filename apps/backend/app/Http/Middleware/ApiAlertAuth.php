<?php

namespace App\Http\Middleware;

use App\Services\ApiService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAlertAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $bearerToken = $request->bearerToken();
        $alert = ApiService::AlertRuleByToken($bearerToken);
        if ($alert) {
            return $next($request->merge(['alert' => $alert, "apiToken" => $bearerToken]));
        }
        abort(Response::HTTP_UNAUTHORIZED);
    }
}
