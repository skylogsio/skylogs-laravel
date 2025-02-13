<?php

namespace App\Services;

use App\Models\AlertInstance;
use App\Models\AlertRule;
use App\Models\AlertRuleGrafana;
use App\Models\Endpoint;
use App\Models\GrafanaInstance;
use App\Models\SentryWebhookAlert;
use App\Utility\Call;
use App\Utility\Constants;
use App\Utility\SMS;
use App\Utility\Telegram;

class GrafanaInstanceService
{

    public static function getRules($instance = null): array
    {
//        $url .= "/api/v1/";
        if (empty($instance) || is_array($instance)) {
            return self::getAllRules($instance);
        } else {
            return self::getRulesInstance($instance);
        }

    }

    private static function getAllRules($instance)
    {

        if(empty($instance)){
            $prometheusAll = GrafanaInstance::all();
        } else {
            $prometheusAll = GrafanaInstance::whereIn("name", $instance)->get();
        }


        $alerts = [];

        foreach ($prometheusAll as $pro) {

            try {

                if (!empty($pro->username) && !empty($pro->password)) {
                    $response = \Http::acceptJson()->withBasicAuth($pro->username, $pro->password)->get($pro->url . "/api/v1/provisioning/alert-rules")->json();
                } else {
                    $response = \Http::acceptJson()->get($pro->url . "/api/v1/provisioning/alert-rules")->json();
                }
                foreach ($response as $alert) {

                    $model = new AlertRuleGrafana();
                    $model->instance = $pro->name;
                    $model->ruleGroup = $alert["ruleGroup"];
                    $model->title = $alert["title"];
                    $model->annotations = $alert['annotations'];
                    $model->labels = $alert['labels'];
                    $alerts[] = $model;
                }


            } catch (\Exception $e) {

            }


        }

        return $alerts;
    }

    private static function getRulesInstance($instance)
    {

        $pro = GrafanaInstance::where("name", $instance)->firstOrFail();
        $alerts = [];

        try {

            if (!empty($pro->username) && !empty($pro->password)) {
                $response = \Http::acceptJson()->withBasicAuth($pro->username, $pro->password)->get($pro->url . "/api/v1/provisioning/alert-rules");
                $body = $response->json();
            } else {
                $response = \Http::acceptJson()->get($pro->url . "/api/v1/provisioning/alert-rules");
                $body = $response->json();
            }
            if ($response->status() == 200)
                foreach ($body as $alert) {
                    $model = new AlertRuleGrafana();
                    $model->instance = $pro->name;
                    $model->ruleGroup = $alert["ruleGroup"] ?? "";
                    $model->title = $alert["title"] ?? "";
                    $model->annotations = $alert['annotations'] ?? "";
                    $model->labels = $alert['labels'] ?? "";
                    $alerts[] = $model;
                }


        } catch (\Exception $e) {

        }


        return $alerts;
    }

    public static function getTriggered(): array
    {
        $prometheusAll = GrafanaInstance::all();
        $alerts = [];

        foreach ($prometheusAll as $pro) {

            try {

                if (!empty($pro->username) && !empty($pro->password)) {
                    $response = \Http::acceptJson()->withBasicAuth($pro->username, $pro->password)->get($pro->url . "/api/v1/alerts")->json();
                } else {
                    $response = \Http::acceptJson()->get($pro->url . "/api/v1/alerts")->json();
                }

                $arr = $response['data']['alerts'];
                foreach ($arr as &$alert) {
                    $alert['instance'] = $pro->name;
                    $alerts[] = $alert;
                }
            } catch (\Exception $exception) {
                $arr = [];
            }
        }
        return $alerts;

    }

}
