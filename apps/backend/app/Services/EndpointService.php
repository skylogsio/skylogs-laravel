<?php

namespace App\Services;

use App\Enums\AlertRuleType;
use App\Jobs\SendNotifyJob;
use App\Models\AlertInstance;
use App\Models\AlertRule;
use App\Models\Endpoint;
use App\Models\SentryWebhookAlert;
use App\Models\User;
use App\Utility\Call;
use App\Utility\Constants;
use App\Utility\SMS;
use App\Utility\Telegram;
use Illuminate\Support\Facades\Cache;

class EndpointService
{

    public static function SelectableUserEndpoint(User $user, AlertRule $alert = null)
    {
        $adminUserId = User::where('username', 'admin')->first()->_id;

        if ($user->hasRole("admin")) {
            return Cache::remember("admin_endpoints",3600, fn() => Endpoint::get());
        }

        if($alert) {
            $alertUserIds = $alert->user_ids ?? [];
        } else {
            return Cache::remember("user_global_endpoints:$user->id",3600, fn() => Endpoint::whereIn("user_id", [$adminUserId, $user->_id])->get());
//            return Endpoint::whereIn("user_id", [$adminUserId, $user->_id])->get();
        }

        if ($alert->user_id == $user->_id) {
            return Cache::remember("user_global_endpoints:$user->id",3600, fn() => Endpoint::whereIn("user_id", [$adminUserId, $user->_id])->get());
//            return Endpoint::whereIn("user_id", [$adminUserId, $user->_id])->get();
        } elseif(in_array($user->_id, $alertUserIds)) {
            return Cache::remember("user_endpoints:$user->id",3600, fn() => Endpoint::where("user_id", $user->_id)->get());
//            return Endpoint::where("user_id", $user->_id)->get();
        }

        return collect();

    }

    public static function ResolveAlert(mixed $post)
    {
        $alert = AlertInstance::where('alertname', $post['alertname'])
            ->where('instance', $post['instance'])
            ->first();

        if ($alert) {
            if ($alert->state == AlertInstance::FIRE) {

                $alert->state = AlertInstance::RESOLVED;
                $alert->description = $post['description'] ?? "";
                $alert->summary = $post['summary'] ?? "";

                $file = !empty($post['file']) ? $post['file'] : null;

                if (!empty($file)) {
                    $filename = $file->getClientOriginalName();
                    $path = $file->storeAs("api_alert_files", $file->hashName());
                    $alert->file = $path;
                    $alert->fileName = $filename;
                } else {
                    $alert->file = null;
                    $alert->fileName = null;
                }

                $alert->save();
                SendNotifyService::CreateNotify(SendNotifyJob::API_RESOLVE, $alert,$alert->alertRule->_id);


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

        $alertRule = AlertRule::where('alertname', $post['alertname'])->where("type", Constants::API)
            ->first();

        if (!$alertRule) {
            return [
                "status" => false,
                "message" => "Alertname Doesn't Exists."
            ];
        }

        $alert = AlertInstance::where('alertname', $post['alertname'])
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


        $alertRule = AlertRule::where('alertname', $post['alertname'])->where("type", Constants::NOTIFICATION)
            ->first();
        if (!$alertRule) {
            return [
                "status" => false,
                "message" => "Alertname Doesn't Exists."
            ];
        }


        $alert = AlertInstance::updateOrCreate([
            "alertRule_id" => $alertRule->_id,
            "alert_rule_id" => $alertRule->_id,
            "alertname" => $post['alertname'],
            "instance" => $post['instance'],
        ], [
            "state" => AlertInstance::NOTIFICATION,
            "description" => $post['description'] ?? null,
        ]);

        $file = !empty($post['file']) ? $post['file'] : null;

        if (!empty($file)) {
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs("notification_alert_files", $file->hashName());
            $alert->file = $path;
            $alert->fileName = $filename;
        } else {
            $alert->file = null;
            $alert->fileName = null;
        }

        SendNotifyService::CreateNotify(SendNotifyJob::API_NOTIFICATION, $alert,$alertRule->_id);

        $alert->createHistory();

        return [
            'status' => true,
            'message' => 'Done'
        ];


    }

    public static function AlertRuleByToken($token)
    {
        $alertRules = Cache::remember("api_alerts",3600*24,function(){
            return AlertRule::where("type",AlertRuleType::API)->get();
        });

        $alert = $alertRules->where("api_token", $token)->first();

        if ($alert) {
            return $alert;
        }

        return null;
    }

}
