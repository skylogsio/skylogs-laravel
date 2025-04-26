<?php

namespace App\Services;

use App\Jobs\SendNotifyJob;
use App\Models\AlertInstance;
use App\Models\AlertRule;
use App\Models\ApiAlertHistory;
use App\Models\SentryWebhookAlert;
use App\Helpers\Call;
use App\Helpers\Constants;
use App\Helpers\SMS;
use App\Helpers\Telegram;
use Carbon\Carbon;
use Illuminate\Http\Request;
use MongoDB\BSON\UTCDateTime;

class ServiceAlertRuleService
{

    public static function GetAlertRules()
    {
//        $prometheusAlertRules = PrometheusInstanceService::getRules();
        $sentryAlertRules = SentryService::getIssueRules();

        return $sentryAlertRules;
    }


}
