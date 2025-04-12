<?php

namespace App\Models;

use App\Enums\AlertRuleType;
use App\Interfaces\Messageable;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class AlertRule extends Model implements Messageable
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
        return $this->belongsTo(User::class);
    }

    public function prometheusCheck()
    {
        return $this->hasOne(PrometheusCheck::class, "alert_rule_id", "_id");
    }

    public function grafanaWebhook()
    {

        if ($this->type == AlertRuleType::GRAFANA && $this->state == self::CRITICAL) {
            return $this->hasOne(GrafanaWebhookAlert::class, "alert_rule_id", "_id")->orderByDesc("_id");
        }
        return null;
    }

    public function sentryWebhook()
    {
        if ($this->type == AlertRuleType::SENTRY && $this->state == self::CRITICAL) {
            return $this->hasOne(SentryWebhookAlert::class, "alert_rule_id", "_id")->orderByDesc("_id");
        }
        return null;
    }

    public function metabaseWebhook()
    {
        if ($this->type == AlertRuleType::METABASE) {
            return $this->hasOne(MetabaseWebhookAlert::class, "alert_rule_id", "_id")->orderByDesc("_id");
        }
        return null;
    }

    public function apiInstances()
    {
        if ($this->type == AlertRuleType::API) {
            $instances = AlertInstance::where('alertname', $this->alertname)->orderByDesc("state")->orderByDesc("_id")->get();
            return $instances;
        }
        return null;

    }

    public function notificationInstances()
    {
        if ($this->type == AlertRuleType::NOTIFICATION) {
            $instances = AlertInstance::where('alertname', $this->alertname)
                ->where("state", AlertInstance::NOTIFICATION)
                ->orderByDesc("_id")->limit(10)->get();
            return $instances;
        }
        return null;

    }

    public function isSilent(): bool
    {
        if (empty($this->silent_user_ids)) return false;
        if (in_array(\Auth::user()->_id, $this->silent_user_ids)) return true;
        return false;
    }

    public function silent()
    {
        $this->push("silent_user_ids", \Auth::user()->_id, true);
        $this->save();
    }

    public function unSilent()
    {
        $this->pull("silent_user_ids", \Auth::user()->_id);
        $this->save();
    }

    public function getStatus(): string|int
    {

        switch ($this->type) {
            case AlertRuleType::API:
                $alertCount = AlertInstance::where('alert_rule_id', $this->id)
                    ->where("state", AlertInstance::FIRE)->count();
                if ($alertCount == 0) {
                    return self::RESOlVED;
                }
                return $alertCount;
            case AlertRuleType::SENTRY:
                if (empty($this->state)) {
                    return self::UNKNOWN;
                } else {
                    return $this->state;
                }

            case AlertRuleType::METABASE:
                if (empty($this->state)) {
                    return self::UNKNOWN;
                } else {
                    return $this->state;
                }
            case AlertRuleType::ZABBIX:
                if (empty($this->state)) {
                    return self::UNKNOWN;
                } else {
                    return $this->state;
                }

            case AlertRuleType::PROMETHEUS:
                $alert = PrometheusCheck::where('alert_rule_id', $this->_id)->first();
                if (!$alert || $alert->state == PrometheusCheck::RESOLVED) {
                    return self::RESOlVED;
                } else {
                    if (!empty($alert->alerts))
                        return count($alert->alerts);
                    else {
                        return self::CRITICAL;
                    }
                }
            case AlertRuleType::GRAFANA:
                if (empty($this->state)) {
                    return self::UNKNOWN;
                } else {
                    return $this->state;
                }
            case AlertRuleType::HEALTH:
                $check = HealthCheck::where('alert_rule_id', $this->_id)->first();
                if (empty($check) || $check->state == HealthCheck::UP) {
                    return self::RESOlVED;
                } else {
                    return self::CRITICAL;
                }
            case AlertRuleType::ELASTIC:
                $check = ElasticCheck::where('alert_rule_id', $this->_id)->first();
                if (empty($check) || $check->state == ElasticCheck::RESOLVED) {
                    return self::RESOlVED;
                } else {
                    return self::CRITICAL;
                }
            case AlertRuleType::NOTIFICATION:
                return self::UNKNOWN;
            case AlertRuleType::PMM:
                // TODO
                return self::UNKNOWN;
            case AlertRuleType::SPLUNK:
                // TODO
                return self::UNKNOWN;

        }
        return self::UNKNOWN;
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
