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


    public function selectableUserEndpoint(User $user, AlertRule $alert = null)
    {

        if ($user->isAdmin()) {
            return Cache::tags(['endpoint', 'admin'])
                ->rememberForever("endpoint:admin",  fn() => Endpoint::get());
        }

        if ($alert) {
            $alertUserIds = $alert->userIds ?? [];
        } else {
            return Cache::tags(['endpoint', $user->id])
                ->rememberForever("endpoint:global:$user->id",  fn() => Endpoint::where("userId",  $user->_id)->orWhere('isPublic',true)->get());
        }

        if ($alert->userId == $user->_id) {
            return Cache::tags(['endpoint', $user->id])
                ->rememberForever("endpoint:global:$user->id",  fn() => Endpoint::where("userId",  $user->_id)->orWhere('isPublic',true)->get());
        } elseif (in_array($user->_id, $alertUserIds)) {
            return Cache::tags(['endpoint', $user->id])
                ->rememberForever("endpoint:user:$user->id",  fn() => Endpoint::where("userId", $user->_id)->get());
        }

        return collect();

    }

    public function countUserEndpointAlert(User $user, AlertRule $alert = null)
    {
        $selectableEndpoints = $this->selectableUserEndpoint($user, $alert);
        $alertEndpoints = collect($alert->endpointIds);
        return $selectableEndpoints->pluck("id")->intersect($alertEndpoints)->count();
    }

    public function deleteEndpointOfAlertRules(Endpoint $endpoint) :void
    {
        foreach (app(AlertRuleService::class)->getAlertsDB() as $alertRule) {
            $alertRule->pull('endpointIds',$endpoint->_id);
        }
    }
    public function flushCache(): void
    {
        Cache::tags(['endpoint'])->flush();
    }


    public function ChangeOwnerAll(User $fromUser,User $toUser)
    {
        $endpoints = Endpoint::where("userId", $fromUser->id)->get();
        foreach ($endpoints as $endpoint) {
            $endpoint->userId = $toUser->id;
            $endpoint->user_id = $toUser->id;
            $endpoint->save();
        }
    }


}
