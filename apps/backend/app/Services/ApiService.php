<?php

namespace App\Services;

use App\Enums\AlertRuleType;
use App\Jobs\SendNotifyJob;
use App\Models\AlertInstance;
use App\Models\AlertRule;
use Illuminate\Support\Facades\Cache;

class ApiService
{

    public static function FireAlert($post): array
    {
        $alertRule = AlertRule::firstWhere('api_token', $post['api_token']);

        if (!$alertRule) {
            return [
                "status" => false,
                "message" => "Alertname Doesn't Exists."
            ];
        }

        $alert = AlertInstance::where('alert_rule_id', $alertRule->id)
            ->where('instance', $post['instance'])
            ->first();

        if ($alert) {
            if ($alert->state == AlertInstance::RESOLVED) {

                $alert->state = AlertInstance::FIRE;
                $alert->description = $post['description'] ?? "";
                $alert->summary = $post['summary'] ?? "";

                $alert->save();

                SendNotifyService::CreateNotify(SendNotifyJob::API_FIRE, $alert, $alertRule->_id);

                $apiHistory = $alert->createHistory();
                $alert->createStatusHistory($apiHistory);

                return [
                    'status' => true,
                    'message' => 'Activated'
                ];
            } else if ($alert->state == AlertInstance::FIRE) {

                $alert->state = AlertInstance::FIRE;
                $alert->description = $post['description'] ?? "";
                $alert->summary = $post['summary'] ?? "";

                $alert->save();

                SendNotifyService::CreateNotify(SendNotifyJob::API_FIRE, $alert, $alertRule->_id);

                $apiHistory = $alert->createHistory();
                $alert->createStatusHistory($apiHistory);

                return [
                    'status' => true,
                    'message' => 'Already Active'
                ];
            }
        } else {

            $model = new AlertInstance();
            $model->alertRule_id = $alertRule->_id;
            $model->alert_rule_id = $alertRule->_id;
            $model->alert_rule_name = $alertRule->name;
            $model->instance = $post['instance'];
            $model->job = $post['job'] ?? "";

            $model->state = AlertInstance::FIRE;

            $model->description = $post['description'] ?? "";
            $model->summary = $post['summary'] ?? "";


            if ($model->save()) {

                SendNotifyService::CreateNotify(SendNotifyJob::API_FIRE, $model, $alertRule->_id);

                $apiHistory = $model->createHistory();
                $model->createStatusHistory($apiHistory);
                return [
                    'status' => true,
                    'message' => 'Activated'
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'error'
                ];
            }
        }

        return [
            'status' => false,
        ];

    }

    public static function ResolveAlert(mixed $post)
    {
        $alertRule = AlertRule::firstWhere('api_token', $post['api_token']);

        $alert = AlertInstance::where('alert_rule_id', $alertRule['id'])
            ->where('instance', $post['instance'])
            ->first();

        if ($alert) {
            if ($alert->state == AlertInstance::FIRE) {

                $alert->state = AlertInstance::RESOLVED;
                $alert->description = $post['description'] ?? "";
                $alert->summary = $post['summary'] ?? "";

                $alert->save();
                SendNotifyService::CreateNotify(SendNotifyJob::API_RESOLVE, $alert, $alert->alertRule->_id);


                $apiHistory = $alert->createHistory();
                $alert->createStatusHistory($apiHistory);
                return [
                    'status' => true,
                    'message' => 'Stopped'
                ];
            } else {


                return [
                    'status' => true,
                    'message' => 'Already Stopped'
                ];
            }
        } else {
            return [
                'status' => false,
                'message' => 'Alert Instance Doesn\'t Exists'
            ];
        }
    }

    public static function StatusAlert(mixed $post)
    {
        $alertRule = AlertRule::firstWhere('api_token', $post['api_token']);


        if (!$alertRule) {
            return [
                "status" => false,
                "message" => "Alertname Doesn't Exists."
            ];
        }

        $alert = AlertInstance::where('alert_rule_id', $alertRule['id'])
            ->where('instance', $post['instance'])
            ->first();

        if ($alert) {
            return [
                'status' => true,
                "isFire" => $alert->state == AlertInstance::FIRE,
            ];
        } else {
            return [
                'status' => true,
                'isFire' => false
            ];
        }
    }

    public static function NotificationAlert(mixed $post)
    {

        $alertRule = AlertRule::firstWhere('api_token', $post['api_token']);

        if (!$alertRule) {
            return [
                "status" => false,
                "message" => "Alertname Doesn't Exists."
            ];
        }


        $alert = AlertInstance::updateOrCreate([
            "alert_rule_id" => $alertRule->_id,
            "alert_rule_name" => $alertRule['name'],
            "instance" => $post['instance'],
        ], [
            "state" => AlertInstance::NOTIFICATION,
            "description" => $post['description'] ?? null,
        ]);

        SendNotifyService::CreateNotify(SendNotifyJob::API_NOTIFICATION, $alert, $alertRule->_id);

        $alert->createHistory();

        return [
            'status' => true,
            'message' => 'Done'
        ];


    }

    public static function AlertRuleByToken($token)
    {
        $alertRules = Cache::tags(['alert_rule','api'])->rememberForever("alert_rule:api", function () {
            return AlertRule::where("type", AlertRuleType::API)->get();
        });

        $alert = $alertRules->where("api_token", $token)->first();

        if ($alert) {
            return $alert;
        }

        return null;
    }

}
