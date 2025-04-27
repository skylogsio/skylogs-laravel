<?php

namespace App\Http\Controllers\V1\Webhooks;


use App\Http\Controllers\Controller;
use App\Jobs\SendNotifyJob;
use App\Models\AlertRule;
use App\Models\MetabaseWebhookAlert;
use App\Models\SentryWebhookAlert;
use App\Models\ZabbixWebhookAlert;
use App\Services\GrafanaService;
use App\Services\SendNotifyService;
use App\Helpers\Constants;
use Illuminate\Http\Request;


class WebhookAlertsController extends Controller
{

    public function GrafanaWebhook(Request $request, $instance)
    {

        $alerts = $request->alerts;

        foreach ($alerts as &$alert) {
            $alert['instance'] = $instance;
        }
        $alertRules = AlertRule::where('type', Constants::GRAFANA)->get();

        $matchedAlerts = GrafanaService::CheckMatchedAlerts($request->all(), $alerts, $alertRules,);
        GrafanaService::SaveMatchedAlerts($instance, $request->all(), $matchedAlerts);
//        \Log::debug("getAlerts",$request->all());
        return [
            "status" => true,
        ];
    }

    public function SentryWebhook(Request $request)
    {
        $post = $request->post();

        $model = new SentryWebhookAlert();

        if ($model->CustomSave($post)) {

            SendNotifyService::CreateNotify(SendNotifyJob::SENTRY_WEBHOOK, $model, $model->alertRuleId);

            return [
                "status" => true,
            ];

        } else {

            return [
                "status" => false,
            ];

        }
    }

    public function ZabbixWebhook(Request $request)
    {

//        $file = fopen("text.txt", 'a+');
//        fwrite($file, json_encode($request->post()));
//        fwrite($file, "\n\n\n");
//        fclose($file);

//
//        return [
//            "status" => true,
//        ];

        $post = $request->post();

        $model = new ZabbixWebhookAlert();

        if ($model->CustomSave($post)) {

            SendNotifyService::CreateNotify(SendNotifyJob::ZABBIX_WEBHOOK, $model, $model->alertRuleId);
            return response()->json([
                "status" => true,
            ]);

        } else {

            return [
                "status" => false,
            ];

        }
    }

    public function UpdamusWebhook(Request $request)
    {

        return [
            "status" => true,
        ];

    }
    public function MetabaseWebhook(Request $request)
    {


        $post = $request->post();

        $model = new MetabaseWebhookAlert();

        if ($model->CustomSave($post)) {

            SendNotifyService::CreateNotify(SendNotifyJob::METABASE_WEBHOOK, $model, $model->alertRuleId);

            return [
                "status" => true,
            ];

        } else {

            return [
                "status" => false,
            ];

        }

    }

}
