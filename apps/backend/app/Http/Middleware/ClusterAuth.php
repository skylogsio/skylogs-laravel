<?php

namespace App\Http\Middleware;

use App\Enums\ClusterType;
use App\Services\ClusterService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClusterAuth
{
    public function __construct(protected ClusterService $clusterService) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->clusterService->type() == ClusterType::MASTER){
            $bearerToken = $request->bearerToken();
            $alert = ApiService::AlertRuleByToken($bearerToken);
            if ($alert) {
                return $next($request->merge(['alert' => $alert, "apiToken" => $bearerToken]));
            }
            abort(Response::HTTP_UNAUTHORIZED);
        }
        abort(Response::HTTP_VERSION_NOT_SUPPORTED);

    }
}
