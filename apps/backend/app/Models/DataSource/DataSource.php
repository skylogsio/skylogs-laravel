<?php

namespace App\Models\DataSource;

use App\Enums\DataSourceType;
use App\Models\BaseModel;
use App\Models\User;
use MongoDB\Laravel\Relations\BelongsTo;

class DataSource extends BaseModel
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

    public function getStatus()
    {
        return self::UNKNOWN;
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

    public function isOk(){
//        return PrometheusInstanceService::getHealthCheck($this);
    }

    public function prometheusGetRulesUrl(){
        return $this->url . "/api/v1/rules";
    }
    public function prometheusGetLabelsUrl(){
        return $this->url . "/api/v1/labels";
    }
    public function prometheusGetLabelsValueUrl($label){
        return $this->url . "/api/v1/label/$label/values";
    }

    public function prometheusGetAlertsUrl(){
        return $this->url . "/api/v1/alerts";
    }
    public function prometheusGetQueryUrl($metric){
        return $this->url . "/api/v1/query?query=$metric";
    }
}
