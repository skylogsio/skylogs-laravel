<?php

namespace App\Http\Controllers\V1\AlertRule;


use App\Enums\AlertRuleType;
use App\Enums\Constants;
use App\Enums\DataSourceType;

use App\Http\Controllers\Controller;
use App\Jobs\SendNotifyJob;
use App\Models\AlertInstance;
use App\Models\AlertRule;
use App\Models\ApiAlertHistory;
use App\Models\DataSource\DataSource;
use App\Models\ElasticHistory;
use App\Models\Endpoint;
use App\Models\GrafanaInstance;
use App\Models\GrafanaWebhookAlert;
use App\Models\GroupAlertRule;
use App\Models\HealthCheck;
use App\Models\ElasticCheck;
use App\Models\HealthHistory;
use App\Models\MetabaseWebhookAlert;
use App\Models\PrometheusCheck;
use App\Models\PrometheusHistory;
use App\Models\PrometheusInstance;
use App\Models\SentryWebhookAlert;
use App\Models\Service;

use App\Models\User;
use App\Models\ZabbixWebhookAlert;
use App\Services\AlertRuleService;
use App\Services\DataSourceService;
use App\Services\GrafanaInstanceService;
use App\Services\PrometheusInstanceService;
use App\Services\SendNotifyService;

use App\Services\TagService;
use Carbon\Carbon;
use Excel;
use Illuminate\Console\View\Components\Alert;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\MessageBag;
use MongoDB\BSON\UTCDateTime;

class NotifyController extends Controller
{


    public function Create($id)
    {
        $adminUserId = User::where('username', 'admin')->first()->_id;

        $alert = AlertRule::where('_id', $id)->first();
        $currentUser = \Auth::user();

        $alertUserIds = $alert->user_ids ?? [];
        $selectableEndpoints = [];
        if ($currentUser->hasRole("admin")) {
            $selectableEndpoints = Endpoint::get();
        } elseif ($alert->user_id == $currentUser->_id) {
            $selectableEndpoints = Endpoint::whereIn("user_id", [$adminUserId, $currentUser->_id])->get();
        } elseif(in_array($currentUser->_id, $alertUserIds)) {
            $selectableEndpoints = Endpoint::where("user_id", $currentUser->_id)->get();
        }

        $alertEndpoints = [];
        if (!empty($alert->endpoint_ids)) {
            if ($currentUser->hasRole("admin")) {
                $alertEndpoints = Endpoint::whereIn("_id", $alert->endpoint_ids)->get();
            } elseif ($alert->user_id == $currentUser->_id) {
                $alertEndpoints = Endpoint::whereIn("_id", $alert->endpoint_ids)->whereIn('user_id', [$adminUserId, $currentUser->_id])->get();
            } elseif(in_array($currentUser->_id, $alertUserIds)) {
                $alertEndpoints = Endpoint::whereIn("_id", $alert->endpoint_ids)->where('user_id', $currentUser->_id)->get();
            }
        }

        return response()->json(compact('alertEndpoints', 'selectableEndpoints'));
    }

    public function CreateBatch()
    {
        $adminUserId = User::where('username', 'admin')->first()->_id;

        $selectableEndpoints = Endpoint::whereIn("user_id", [$adminUserId, \Auth::user()->_id])->get();

        return response()->json(compact('selectableEndpoints'));
    }

    public function Test($id)
    {
        $user = Auth::user();
        $alert = AlertRule::where('_id', $id)->first();
        $access = AlertRuleService::HasUserAccessAlert($user, $alert);
        if (!$access) {
            abort(403);
        }
        SendNotifyService::CreateNotify(SendNotifyJob::ALERT_RULE_TEST, $alert, $alert->_id);

        return response()->json(['status' => true]);
    }

    public function Store(Request $request, $id)
    {

//        if ($va->passes()) {
        $currentUser = Auth::user();
        $isAdmin = $currentUser->hasRole("admin");
        if ($request->has("endpoint_ids") && !empty($request->post("endpoint_ids"))) {
            $adminUserId = User::where('username', 'admin')->first()->_id;

            $alert = AlertRule::where('_id', $id)->first();

            foreach ($request->endpoint_ids as $end) {
                $exists =false;
                $alertUserIds = $alert->user_ids ?? [];
                if ($isAdmin) {
                    $exists = true;
                } elseif ($alert->user_id == $currentUser->_id) {
                    $exists = Endpoint::where("id", $end)->whereIn("user_id", [$adminUserId, $currentUser->_id])->get();
                } elseif(in_array($currentUser->_id, $alertUserIds)) {
                    $exists = Endpoint::where("id", $end)->where("user_id", $currentUser->_id)->get();
                }
                if ($exists)
                    $alert->push("endpoint_ids", $end, true);
            }
            $alert->save();

        }

        return response()->json(['status' => true]);
    }

    public function StoreBatch(Request $request)
    {

        $currentUser = Auth::user();
        $isAdmin = $currentUser->hasRole("admin");
        $alertIds = [];
        if ($request->has("alertIds") && !empty($request->post("alertIds"))) {
            $alertIds = $request->post("alertIds");
        }
        if ($request->has("endpoints") && !empty($request->post("endpoints"))) {
            $adminUserId = User::where('username', 'admin')->first()->_id;
            $adminEndpoints = Endpoint::where("user_id", $adminUserId)
                ->get()
                ->pluck("_id")->toArray();
            $currentUserId = Auth::user()->_id;

            foreach ($alertIds as $id) {
                $alert = AlertRule::where('_id', $id)->first();

                foreach ($request->endpoints as $endpointId) {
                    $exists =false;
                    $alertUserIds = $alert->user_ids ?? [];
                    if ($isAdmin) {
                        $exists = true;
                    } elseif ($alert->user_id == $currentUser->_id) {
                        $exists = Endpoint::where("id", $endpointId)->whereIn("user_id", [$adminUserId, $currentUser->_id])->get();
                    } elseif(in_array($currentUser->_id, $alertUserIds)) {
                        $exists = Endpoint::where("id", $endpointId)->where("user_id", $currentUser->_id)->get();
                    }
                    if ($exists)
                        $alert->push("endpoint_ids", $endpointId, true);

                }
                $alert->save();
            }

        }

        return response()->json(['status' => true]);
    }

    public function Delete($alertId, $endpointId)
    {

        $alert = AlertRule::where('_id', $alertId)->first();
        $alert->pull("endpoint_ids", $endpointId);
        $alert->save();
        return response()->json(['status' => true]);
    }




}
