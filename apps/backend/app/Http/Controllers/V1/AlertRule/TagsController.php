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
use Illuminate\Container\Attributes\Tag;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\MessageBag;
use MongoDB\BSON\UTCDateTime;

class TagsController extends Controller
{


    public function All()
    {

        return response()->json(TagService::All());
    }

    public function Create(Request $request, $id)
    {
        $alert = AlertRule::where('_id', $id)->first();
        $currentUser = Auth::user();
        if (!AlertRuleService::HasAdminAccessAlert($currentUser, $alert)) {
            abort(403);
        }
        $tags = $alert->tags ?? [];
        return  response()->json($tags);
    }

    public function Store(Request $request, $id)
    {

        if(!empty($request->tags) && !is_array($request->tags)) {
            abort(422);
        }
        $alert = AlertRule::where('_id', $id)->first();
        $currentUser = Auth::user();
        $access = AlertRuleService::HasAdminAccessAlert($currentUser, $alert);

        $tags = collect($request->tags ?? [])->map(fn($item) => trim($item))->unique()->toArray();
        if (!$access) {
            abort(403);
        }

        $alert->tags = $tags;
        $alert->save();
        TagService::FlushCache();

        return response()->json(["status" => true,]);

    }



}
