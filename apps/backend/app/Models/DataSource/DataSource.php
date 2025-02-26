<?php

namespace App\Models\DataSource;

use App\Enums\DataSourceType;
use App\interfaces\Messageable;
use App\Models\AlertInstance;
use App\Models\ElasticCheck;
use App\Models\Endpoint;
use App\Models\GrafanaWebhookAlert;
use App\Models\HealthCheck;
use App\Models\MetabaseWebhookAlert;
use App\Models\PrometheusCheck;
use App\Models\SentryWebhookAlert;
use App\Models\Service;
use App\Models\ServiceCheck;
use App\Models\User;
use App\Utility\Constants;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class DataSource extends Model
{

    protected $casts = [
        "type" => DataSourceType::class,
    ];
    public const UNKNOWN = "unknown";
    public const WARNING = "warning";
    public const CRITICAL = "critical";
    public const TRIGGERED = "triggered";
    public const RESOlVED = "resolved";

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

        if ($this->type == Constants::GRAFANA && $this->state == self::CRITICAL) {
            return $this->hasOne(GrafanaWebhookAlert::class, "alert_rule_id", "_id")->orderByDesc("_id");
        }
        return null;
    }

    public function sentryWebhook()
    {
        if ($this->type == Constants::SENTRY && $this->state == self::CRITICAL) {
            return $this->hasOne(SentryWebhookAlert::class, "alert_rule_id", "_id")->orderByDesc("_id");
        }
        return null;
    }
    public function metabaseWebhook()
    {
        if ($this->type == Constants::METABASE ) {
            return $this->hasOne(MetabaseWebhookAlert::class, "alert_rule_id", "_id")->orderByDesc("_id");
        }
        return null;
    }

    public function apiInstances()
    {
        if ($this->type == Constants::API) {
            $instances = AlertInstance::where('alertname', $this->alertname)->orderByDesc("state")->orderByDesc("_id")->get();
            return $instances;
        }
        return null;

    }

    public function notificationInstances()
    {
        if ($this->type == Constants::NOTIFICATION) {
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

    public function getStatus()
    {
        switch ($this->type) {
            case Constants::API:
                $alert = AlertInstance::where('alertname', $this->alertname)->where("state", AlertInstance::FIRE)->count();
                if ($alert == 0) {
                    return self::RESOlVED;
                }
                return $alert;
                break;
            case Constants::SENTRY:
                if (empty($this->state)) {
                    return self::UNKNOWN;
                } else {
                    return $this->state;
                }
                break;
            case Constants::METABASE:
                if (empty($this->state)) {
                    return self::UNKNOWN;
                } else {
                    return $this->state;
                }
                break;
            case Constants::ZABBIX:
                if (empty($this->state)) {
                    return self::UNKNOWN;
                } else {
                    return $this->state;
                }
                break;

            case Constants::PROMETHEUS:
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
                break;
            case Constants::GRAFANA:
                if (empty($this->state)) {
                    return self::UNKNOWN;
                } else {
                    return $this->state;
                }
                break;
            case Constants::HEALTH:
                $check = HealthCheck::where('alert_rule_id', $this->_id)->first();
                if (empty($check) || $check->state == HealthCheck::UP) {
                    return self::RESOlVED;
                } else {
                    return self::CRITICAL;
                }
            case Constants::ELASTIC:
                $check = ElasticCheck::where('alert_rule_id', $this->_id)->first();
                if (empty($check) || $check->state == ElasticCheck::RESOLVED) {
                    return self::RESOlVED;
                } else {
                    return self::CRITICAL;
                }
                break;
            case Constants::NOTIFICATION:
                return self::UNKNOWN;
            case Constants::SERVICE:
                $check = ServiceCheck::where('alert_rule_id', $this->_id)->first();
                if (empty($check) || $check->state == ServiceCheck::UP) {
                    return self::RESOlVED;
                } else {
                    return self::CRITICAL;
                }
        }
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
        $text = $this->alertname;
        $text .= " resolved manually.";
        return $text;
    }

    public function teamsMessage()
    {
        $text = $this->alertname;
        $text .= " resolved manually.";
        return $text;
    }

    public function emailMessage()
    {
        $text = $this->alertname;
        $text .= " resolved manually.";
        return $text;
    }

    public function smsMessage()
    {
        $text = $this->alertname;
        $text .= " resolved manually.";
        return $text;
    }

    public function callMessage()
    {
        $text = $this->alertname;
        $text .= " resolved manually.";
        return $text;
    }
    public function testMessage()
    {
        $text = "Testing ";
        $text .= $this->alertname;
        $text .= " Alert.";
        return $text;
    }
}
