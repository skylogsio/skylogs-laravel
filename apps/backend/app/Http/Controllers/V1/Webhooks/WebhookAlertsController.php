<?php

namespace App\Http\Controllers\V1\Webhooks;


use App\Enums\AlertRuleType;
use App\Enums\DataSourceType;
use App\Http\Controllers\Controller;
use App\Jobs\SendNotifyJob;
use App\Models\AlertRule;
use App\Models\DataSource\DataSource;
use App\Models\GrafanaWebhookAlert;
use App\Models\MetabaseWebhookAlert;
use App\Models\SentryWebhookAlert;
use App\Models\ZabbixWebhookAlert;
use App\Services\DataSourceService;
use App\Services\GrafanaService;
use App\Services\SendNotifyService;
use App\Helpers\Constants;
use Illuminate\Http\Request;


class WebhookAlertsController extends Controller
{

    public function GrafanaWebhook(Request $request, $token)
    {

        $dataSource = DataSourceService::Get(DataSourceType::GRAFANA)
            ->where('webhookToken', $token)
            ->first();
        if (!$dataSource) abort(403);

        $alertRules = AlertRule::where('type', AlertRuleType::GRAFANA)
            ->where('dataSourceIds', $dataSource->id)
            ->get();

        if ($alertRules->isEmpty())
            abort(422,'Alert rule not found');



        $alerts = $request->alerts;

        foreach ($alerts as &$alert) {
            $alert['dataSourceId'] = $dataSource->id;
        }

        $matchedAlerts = GrafanaService::CheckMatchedAlerts($request->all(), $alerts, $alertRules,);
        GrafanaService::SaveMatchedAlerts( $dataSource, $request->all(), $matchedAlerts);

        return [
            "status" => true,
        ];
    }

    public function PmmWebhook(Request $request, $token)
    {

        $dataSource = DataSourceService::Get(DataSourceType::PMM)
            ->where('webhookToken', $token)
            ->first();
        if (!$dataSource) abort(422,'DataSource not found');

        $alertRules = AlertRule::where('type', AlertRuleType::PMM)
            ->where('dataSourceIds', $dataSource->id)
            ->get();

        if ($alertRules->isEmpty())
            abort(422,'Alert rule not found');


        $alerts = $request->alerts;

        foreach ($alerts as &$alert) {
            $alert['dataSourceId'] = $dataSource->id;
        }

        $matchedAlerts = GrafanaService::CheckMatchedAlerts($request->all(), $alerts, $alertRules,);
        GrafanaService::SaveMatchedAlerts( $dataSource, $request->all(), $matchedAlerts);

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
