<?php

namespace App\Models;

use App\Enums\AlertRuleType;
use App\Interfaces\Messageable;
use App\Models\DataSource\DataSource;
use App\Observers\AlertRuleObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

#[ObservedBy(AlertRuleObserver::class)]
class AlertRule extends BaseModel implements Messageable
{

    public const UNKNOWN = "unknown";
    public const WARNING = "warning";
    public const CRITICAL = "critical";
    public const TRIGGERED = "triggered";
    public const RESOlVED = "resolved";

    protected $casts = [
        "type" => AlertRuleType::class,
    ];
    public const DYNAMIC_QUERY_TYPE = "dynamic";
    public const TEXT_QUERY_QUERY_TYPE = "textQuery";

    public $timestamps = true;
    public static $title = "Alert Rule";
    public static $KEY = "alerts";

    protected $guarded = ['id', '_id',];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "userId");
    }

    public function dataSource(): ?BelongsTo
    {
        if ($this->type == AlertRuleType::ELASTIC)
            return $this->belongsTo(DataSource::class, "dataSourceId", "_id");
        else
            return null;
    }

    public function prometheusCheck()
    {
        return $this->hasOne(PrometheusCheck::class, "alertRuleId", "_id");
    }

    public function grafanaWebhook()
    {

        if ($this->type == AlertRuleType::GRAFANA && $this->state == self::CRITICAL) {
            return $this->hasOne(GrafanaWebhookAlert::class, "alertRuleId", "_id")->orderByDesc("_id");
        }
        return null;
    }

    public function sentryWebhook()
    {
        if ($this->type == AlertRuleType::SENTRY && $this->state == self::CRITICAL) {
            return $this->hasOne(SentryWebhookAlert::class, "alertRuleId", "_id")->orderByDesc("_id");
        }
        return null;
    }

    public function metabaseWebhook()
    {
        if ($this->type == AlertRuleType::METABASE) {
            return $this->hasOne(MetabaseWebhookAlert::class, "alertRuleId", "_id")->orderByDesc("_id");
        }
        return null;
    }

    public function apiInstances()
    {
        if ($this->type == AlertRuleType::API) {
            $instances = AlertInstance::where('alertRuleId', $this->_id)->orderByDesc("state")->orderByDesc("_id")->get();
            return $instances;
        }
        return null;

    }

    public function notificationInstances()
    {
        if ($this->type == AlertRuleType::NOTIFICATION) {
            $instances = AlertInstance::where('alertRuleId', $this->_id)
                ->where("state", AlertInstance::NOTIFICATION)
                ->orderByDesc("_id")->limit(10)->get();
            return $instances;
        }
        return null;

    }

    public function isSilent(): bool
    {
        return !empty($this->silentUserIds) && in_array(\Auth::user()->_id, $this->silentUserIds);
    }

    public function silent()
    {
        $this->push("silentUserIds", \Auth::user()->_id, true);
        $this->save();
    }

    public function unSilent()
    {
        $this->pull("silentUserIds", \Auth::user()->_id);
        $this->save();
    }

    public function getStatus(): array
    {
        $alertCount = 0;
        $alertState = self::UNKNOWN;
        switch ($this->type) {
            case AlertRuleType::API:
                $alertState = $this->state ?? self::RESOlVED;
                $alertCount = $alert->fireCount ?? 0;
                break;
            case AlertRuleType::GRAFANA:
            case AlertRuleType::PROMETHEUS:
            case AlertRuleType::SENTRY:
            case AlertRuleType::METABASE:
            case AlertRuleType::ZABBIX:
                $alertState = $this->state ?? self::UNKNOWN;
                break;
            case AlertRuleType::HEALTH:
                $check = HealthCheck::where('alertRuleId', $this->_id)->first();
                if (empty($check) || $check->state == HealthCheck::UP) {
                    $alertState = self::RESOlVED;
                } else {
                    $alertState = self::CRITICAL;
                }
                break;

            case AlertRuleType::ELASTIC:
                $check = ElasticCheck::where('alertRuleId', $this->_id)->first();
                if (empty($check) || $check->state == ElasticCheck::RESOLVED) {
                    $alertState = self::RESOlVED;
                } else {
                    $alertState = self::CRITICAL;
                }
                break;
            case AlertRuleType::NOTIFICATION:
            case AlertRuleType::PMM:
                // TODO
            case AlertRuleType::SPLUNK:
                // TODO
                break;

        }
        return [$alertState, $alertCount];
    }

    public function accessUsers()
    {
        return $this->embedsMany(User::class, "accessUsers");
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, "service_id", "_id");
    }

    public function endpoints()
    {
        return $this->hasMany(Endpoint::class);
//        return Endpoint::whereIn("_id",$this->en)
//        return $this->embedsMany(Endpoint::class,"notifies");
    }

    public static $types = [
        "api" => "Api",
        "notification" => "Notification",
        "sentry" => "Sentry",
        "metabase" => "Metabase",
        "prometheus" => "Prometheus",
        "grafana" => "Grafana",
        "health" => "Health",
        "elastic" => "Elastic",
        "zabbix" => "Zabbix",
    ];

    //########### ONLY FOR MANUALLY RESOLVE
    public function telegramMessage()
    {
        $text = $this->name;
        $text .= " resolved manually.";
        return $text;
    }

    public function teamsMessage()
    {
        $text = $this->name;
        $text .= " resolved manually.";
        return $text;
    }

    public function emailMessage()
    {
        $text = $this->name;
        $text .= " resolved manually.";
        return $text;
    }

    public function smsMessage()
    {
        $text = $this->name;
        $text .= " resolved manually.";
        return $text;
    }

    public function callMessage()
    {
        $text = $this->name;
        $text .= " resolved manually.";
        return $text;
    }

    public function testMessage()
    {
        $text = "Testing ";
        $text .= $this->name;
        $text .= " Alert.";
        return $text;
    }
}
