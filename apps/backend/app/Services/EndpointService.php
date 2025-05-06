<?php

namespace App\Services;

use App\Enums\AlertRuleType;
use App\Jobs\SendNotifyJob;
use App\Models\AlertInstance;
use App\Models\AlertRule;
use App\Models\Endpoint;
use App\Models\SentryWebhookAlert;
use App\Models\User;
use App\Helpers\Call;
use App\Helpers\Constants;
use App\Helpers\SMS;
use App\Helpers\Telegram;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class EndpointService
{


    public static function SelectableUserEndpoint(User $user, AlertRule $alert = null)
    {
        $adminUserId = User::where('username', 'admin')->first()->_id;

        if ($user->isAdmin()) {
            return Cache::tags(['endpoint', 'admin'])
                ->rememberForever("endpoint:admin",  fn() => Endpoint::get());
        }

        if ($alert) {
            $alertUserIds = $alert->userIds ?? [];
        } else {
            return Cache::tags(['endpoint', $user->id])
                ->rememberForever("endpoint:global:$user->id",  fn() => Endpoint::whereIn("user_id", [$adminUserId, $user->_id])->get());
        }

        if ($alert->userId == $user->_id) {
            return Cache::tags(['endpoint', $user->id])
                ->rememberForever("endpoint:global:$user->id",  fn() => Endpoint::whereIn("user_id", [$adminUserId, $user->_id])->get());
        } elseif (in_array($user->_id, $alertUserIds)) {
            return Cache::tags(['endpoint', $user->id])
                ->rememberForever("endpoint:user:$user->id",  fn() => Endpoint::where("user_id", $user->_id)->get());
        }

        return collect();

    }

    public static function CountUserEndpointAlert(User $user, AlertRule $alert = null)
    {
        $selectableEndpoints = self::SelectableUserEndpoint($user, $alert);
        $alertEndpoints = collect($alert->endpointIds);
        return $selectableEndpoints->pluck("id")->intersect($alertEndpoints)->count();
    }

    public static function RefreshAlertRuleEndpoints(Endpoint $endpoint) :void
    {
        foreach (AlertRuleService::GetAlertsDB() as $alertRule) {
            $alertRule->pull('endpointIds',$endpoint->_id);
        }
    }
    public static function FlushCache(): void
    {
        Cache::tags(['endpoint'])->flush();
    }



}
