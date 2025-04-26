<?php

namespace App\Services;

use App\Interfaces\Messageable;
use App\Jobs\SendNotifyJob;
use App\Models\AlertInstance;
use App\Models\AlertRule;
use App\Models\AlertRulePrometheus;
use App\Models\Endpoint;
use App\Models\GrafanaWebhookAlert;
use App\Models\GroupAlertRule;
use App\Models\Log;
use App\Models\Notify;
use App\Models\SentryWebhookAlert;
use App\Models\SilentRule;
use App\Models\ZabbixWebhookAlert;
use App\Helpers\Call;
use App\Helpers\Constants;
use App\Helpers\SMS;
use App\Helpers\Teams;
use App\Helpers\Telegram;
use Illuminate\Support\Collection;

class GroupService
{
    public static function GetEndpointsByAlertId($alertRule)
    {
        $groups = GroupAlertRule::where('alert_ids', $alertRule->_id)->get();
        $endpointIds = collect();
        if ($groups->isNotEmpty()) {
            foreach ($groups as $group) {
                $endpointIds = $endpointIds->merge($group->endpoint_ids);
            }

        } else {

            $endpointIds = $alertRule->endpoint_ids ?? [];
        }
        if ($endpointIds instanceof Collection) {
            return $endpointIds->toArray();
        } else
            return $endpointIds;
    }


}
