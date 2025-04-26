<?php

namespace App\Services;

use App\Interfaces\Messageable;
use App\Jobs\SendNotifyJob;
use App\Models\AlertInstance;
use App\Models\AlertRule;
use App\Models\AlertRulePrometheus;
use App\Models\Endpoint;
use App\Models\GrafanaWebhookAlert;
use App\Models\Log;
use App\Models\Notify;
use App\Models\SentryWebhookAlert;
use App\Models\SilentRule;
use App\Models\ZabbixWebhookAlert;
use App\Helpers\Call;
use App\Helpers\Constants;
use App\Helpers\Email;
use App\Helpers\SMS;
use App\Helpers\Teams;
use App\Helpers\Telegram;
use Illuminate\Support\Collection;

class SendNotifyService
{
    public static function CreateNotify($type, $alert, $alertRuleId = 0)
    {
        $notify = new Notify();
        $notify->type = $type;
        $notify->alert_rule_id = $alertRuleId;

        try {
            $notify->alert = $alert->toArray();
        } catch (\Exception $exception) {
            $notify->alert = $alert;
        }

        if ($type == SendNotifyJob::ALERT_RULE_TEST) {
            $messages = [
                "telegramMessage" => $notify->alertRule->testMessage(),
                "teamsMessage" => $notify->alertRule->testMessage(),
                "emailMessage" => $notify->alertRule->testMessage(),
                "smsMessage" => $notify->alertRule->testMessage(),
                "callMessage" => $notify->alertRule->testMessage(),
            ];

        } else {


            $messages = [
                "telegramMessage" => $alert->telegramMessage(),
                "teamsMessage" => $alert->teamsMessage(),
                "emailMessage" => $alert->emailMessage(),
                "smsMessage" => $alert->smsMessage(),
                "callMessage" => $alert->callMessage(),
            ];

        }
        $notify->messages = $messages;

        $notify->status = Notify::STATUS_CREATED;

        $notify->save();
        SendNotifyJob::dispatch($notify);

        return $notify;
    }

    public static function SendMessage(Notify $notify, $isTest = false)
    {

        if (empty($notify->alertRule) || !($notify->alertRule instanceof AlertRule)) {
            return;
        }
        if (!$isTest && in_array($notify->alertRule->_id, SilentRuleService::getCurrentSilents())) {
            $notify->status = Notify::STATUS_SILENT;
            $notify->save();
            return;
        }

//        $endpointIds = GroupService::GetEndpointsByAlertId($notify->alertRule);


        $endpointIds = $notify->alertRule->endpoint_ids ?? [];
        $silentUserIds = $notify->alertRule->silent_user_ids ?? [];


        $endpoints = Endpoint::whereIn("_id", $endpointIds)->whereNotIn("user_id", $silentUserIds)->get();
//        ds($endpoints,);
        $phones = $endpoints->where("type", "sms")->pluck("value");
        $phonesCalls = $endpoints->where("type", "call")->pluck("value");
        $teamsUrls = $endpoints->where("type", "teams")->pluck("value");
        $emails = $endpoints->where("type", "email")->pluck("value")->toArray();
        $telegrams = $endpoints->where("type", "telegram")->toArray();

        $notify->endpointIds = $endpointIds;
        $notify->silentUserIds = $silentUserIds;


        if ($phones->isNotEmpty()) {
            $result = SMS::sendAlert($phones,
                $notify);
            $notify->resultSms = $result;
        }
        if ($phonesCalls->isNotEmpty()) {
            $result = Call::sendAlert($phonesCalls,
                $notify,
            );
            $notify->resultCall = $result;
        }
        if ($teamsUrls->isNotEmpty()) {
            $result = Teams::sendMessageAlert($teamsUrls,
                $notify,
            );
            $notify->resultTeams = $result;
        }
        if (!empty($telegrams)) {
            $result = Telegram::sendMessageAlert($telegrams,
                $notify
            );
            $notify->resultTelegram = $result;
        }

        if (!empty($emails)) {
            $result = Email::sendMessageAlert($emails,
                $notify
            );
            $notify->resultEmail = $result;
        }

        $notify->save();
    }

}
