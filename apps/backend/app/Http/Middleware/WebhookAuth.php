<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($request->routeIs('webhook.pmm')) {
//            $request->

        }elseif($request->routeIs('webhook.grafana')) {

        }elseif($request->routeIs('webhook.sentry')) {

        }elseif($request->routeIs('webhook.splunk')) {

        }elseif($request->routeIs('webhook.zabbix')){

        }elseif($request->routeIs('webhook.metabase')){

        }
        return $next($request);
    }
}
