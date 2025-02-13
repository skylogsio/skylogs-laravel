<?php

namespace App\Http\Controllers\Api\V1\Webhooks;


use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\Request;


class ApiAlertController extends Controller
{

    public function FireAlert(Request $request)
    {
        if (!$request->has(['alertname', 'instance'])) {
            abort(404, "wrong parameters!");
        }
        $post = $request->all();
        $post['file'] = $request->file('file');
        return ApiService::FireAlert($post);
    }
    public function StopAlert(Request $request)
    {
        if (!$request->has(['alertname', 'instance'])) {
            abort(404, "wrong parameters!");
        }
        $post = $request->all();
        $post['file'] = $request->file('file');
        return ApiService::StopAlert($post);
    }

    public function StatusAlert(Request $request)
    {
        if (!$request->has(['alertname', 'instance'])) {
            abort(404, "wrong parameters!");
        }
        $post = $request->all();
        return ApiService::StatusAlert($post);
    }


    public function Notification(Request $request)
    {
        if (!$request->has(['alertname', 'instance'])) {
            abort(404, "wrong parameters!");
        }
        $post = $request->all();
        $post['file'] = $request->file('file');

        return ApiService::NotificationAlert($post);
    }


}
