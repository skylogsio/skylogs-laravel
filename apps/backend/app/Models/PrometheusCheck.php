<?php

namespace App\Models;

use App\Interfaces\Messageable;
use App\Models\DataSource\DataSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;
use Morilog\Jalali\Jalalian;

class PrometheusCheck extends BaseModel implements Messageable
{

    public $timestamps = true;
    public static $title = "Prometheus Check";
    public static $KEY = "prometheus_check";

    protected $guarded = ['id', '_id',];

    public const RESOLVED = 1;
    public const FIRE = 2;
    public const NOTIFICATION = 3;


    public function alertRule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, "alertRuleId", "_id");
    }


    public function dataSource(): BelongsTo
    {
        return $this->belongsTo(DataSource::class, "dataSourceId", "_id");
    }



    public function createHistory()
    {

        PrometheusHistory::create(
            [
                "alertRuleId" => $this->alertRuleId,
                "alerts" => $this->alerts,
                "state" => $this->state,
            ]
        );
    }

    public function saveWithHistory($matchedAlerts)
    {
        $savedAlerts = collect(\Arr::dot($this->alerts));
        $currentAlerts = collect(\Arr::dot($matchedAlerts));

        $diffs = $savedAlerts->diffAssoc($currentAlerts);
        $diffs2 = $currentAlerts->diffAssoc($savedAlerts);
        if ($diffs->isNotEmpty() || $diffs2->isNotEmpty()) {
            PrometheusHistory::create(
                [
                    "alertRuleId" => $this->alertRuleId,
                    "alerts" => $this->alerts,
                    "state" => $this->state,
                ]
            );
        }

        $this->alerts = $matchedAlerts;
        $this->save();


    }


    public function telegramMessage(): string
    {
        $needLabelArray = ["severity", "job", "namespace", "pod", "reason",];
        $needLabelAnotArray = ["summary","description"];

        $alertRule = $this->alertRule;

        $text = $alertRule->name . "\n\n";
        if (!empty($this->state)) {
            switch ($this->state) {
                case self::RESOLVED:
                    $text .= "State: Resolved ✅" . "\n\n";
                    break;
                case self::FIRE:
                    $text .= "State: Fire 🔥" . "\n\n";
                    break;
            }
        }

//        if (!empty($alertRule->instance)) {
//            $text .= "Instance: " . $alertRule->instance . "\n\n";
//        }

        if ($alertRule->queryType == AlertRule::DYNAMIC_QUERY_TYPE){
            $text .= "AlertName: " . $alertRule->dataSourceAlertName . "\n\n";
        }else{
            $needLabelArray[] = "alertname";
        }

        if (!empty($this->alerts)) {
            foreach ($this->alerts as $alert) {
                if (empty($alert['skylogsStatus']) || $alert['skylogsStatus'] == self::FIRE) {
                    $text .= "Fire 🔥" . "\n";
                }else{
                    $text .= "Resolved ✅" . "\n";

                }

                $text .= "Data Source: " . $alert['dataSourceName'] . "\n";
                if (!empty($alert['labels']))
                    foreach ($needLabelArray as $label) {
                        if (!empty($alert['labels'][$label])) {
                            $text .= "$label : " . $alert['labels'][$label] . "\n";
                        }
                    }
                if (!empty($alert['annotations']))
                    foreach ($needLabelAnotArray as $label) {
                        if (!empty($alert['annotations'][$label])) {
                            $text .= "$label : " . $alert['annotations'][$label] . "\n";
                        }
                    }
                $text .= "\n************\n\n";
            }
        }


        $text .= "date: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }

    public function teamsMessage(): string
    {
        $needLabelArray = ["severity", "job", "namespace", "pod", "reason",];
        $needLabelAnotArray = ["summary","description"];

        $alertRule = $this->alertRule;

        $text = $alertRule->name . "\n\n";
        if (!empty($this->state)) {
            switch ($this->state) {
                case self::RESOLVED:
                    $text .= "State: Resolved ✅" . "\n\n";
                    break;
                case self::FIRE:
                    $text .= "State: Fire 🔥" . "\n\n";
                    break;
            }
        }


        if ($alertRule->queryType == AlertRule::DYNAMIC_QUERY_TYPE){
            $text .= "AlertName: " . $alertRule->dataSourceAlertName . "\n\n";
        }else{
            $needLabelArray[] = "alertname";
        }
        if (!empty($this->alerts) ) {
            foreach ($this->alerts as $alert) {
                if (empty($alert['skylogsStatus']) || $alert['skylogsStatus'] == self::FIRE) {
                    $text .= "Fire 🔥" . "\n";
                }else{
                    $text .= "Resolved ✅" . "\n";

                }

                $text .= "Data Source: " . $alert['dataSourceName'] . "\n";
                if (!empty($alert['labels']))
                    foreach ($needLabelArray as $label) {
                        if (!empty($alert['labels'][$label])) {
                            $text .= "$label : " . $alert['labels'][$label] . "\n";
                        }
                    }
                if (!empty($alert['annotations']))
                    foreach ($needLabelAnotArray as $label) {
                        if (!empty($alert['annotations'][$label])) {
                            $text .= "$label : " . $alert['annotations'][$label] . "\n";
                        }
                    }
                $text .= "\n************\n\n";
            }
        }


        $text .= "date: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }
    public function emailMessage(): string
    {
        $needLabelArray = ["severity", "job", "namespace", "pod", "reason",];
        $needLabelAnotArray = ["summary","description"];

        $alertRule = $this->alertRule;

        $text = $alertRule->name . "\n\n";
        if (!empty($this->state)) {
            switch ($this->state) {
                case self::RESOLVED:
                    $text .= "State: Resolved ✅" . "\n\n";
                    break;
                case self::FIRE:
                    $text .= "State: Fire 🔥" . "\n\n";
                    break;
            }
        }


        if ($alertRule->queryType == AlertRule::DYNAMIC_QUERY_TYPE){
            $text .= "AlertName: " . $alertRule->dataSourceAlertName . "\n\n";
        }else{
            $needLabelArray[] = "alertname";
        }
        if (!empty($this->alerts) ) {
            foreach ($this->alerts as $alert) {
                if (empty($alert['skylogsStatus']) || $alert['skylogsStatus'] == self::FIRE) {
                    $text .= "Fire 🔥" . "\n";
                }else{
                    $text .= "Resolved ✅" . "\n";

                }

                $text .= "Data Source: " . $alert['dataSourceName'] . "\n";
                if (!empty($alert['labels']))
                    foreach ($needLabelArray as $label) {
                        if (!empty($alert['labels'][$label])) {
                            $text .= "$label : " . $alert['labels'][$label] . "\n";
                        }
                    }
                if (!empty($alert['annotations']))
                    foreach ($needLabelAnotArray as $label) {
                        if (!empty($alert['annotations'][$label])) {
                            $text .= "$label : " . $alert['annotations'][$label] . "\n";
                        }
                    }
                $text .= "\n************\n\n";
            }
        }


        $text .= "date: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }

    public function smsMessage(): string
    {
        $needLabelArray = ["severity", "job", "namespace", "pod", "reason",];
        $needLabelAnotArray = ["summary","description"];

        $alertRule = $this->alertRule;

        $text = $alertRule->name . "\n\n";
        if (!empty($this->state)) {
            switch ($this->state) {
                case self::RESOLVED:
                    $text .= "State: Resolved ✅" . "\n\n";
                    break;
                case self::FIRE:
                    $text .= "State: Fire 🔥" . "\n\n";
                    break;
            }
        }


        if ($alertRule->queryType == AlertRule::DYNAMIC_QUERY_TYPE){
            $text .= "AlertName: " . $alertRule->dataSourceAlertName . "\n\n";
        }else{
            $needLabelArray[] = "alertname";
        }
        if (!empty($this->alerts)) {
            foreach ($this->alerts as $alert) {
                if (empty($alert['skylogsStatus']) || $alert['skylogsStatus'] == self::FIRE) {
                    $text .= "Fire 🔥" . "\n";
                }else{
                    $text .= "Resolved ✅" . "\n";

                }

                $text .= "Data Source: " . $alert['dataSourceName'] . "\n";
                if (!empty($alert['labels']))
                    foreach ($needLabelArray as $label) {
                        if (!empty($alert['labels'][$label])) {
                            $text .= "$label : " . $alert['labels'][$label] . "\n";
                        }
                    }
                if (!empty($alert['annotations']))
                    foreach ($needLabelAnotArray as $label) {
                        if (!empty($alert['annotations'][$label])) {
                            $text .= "$label : " . $alert['annotations'][$label] . "\n";
                        }
                    }
                $text .= "\n************\n\n";
            }
        }


        $text .= "date: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }

    public function callMessage(): string
    {
        $needLabelArray = ["severity", "job", "namespace", "pod", "reason",];
        $needLabelAnotArray = ["summary","description"];

        $alertRule = $this->alertRule;

        $text = $alertRule->name . "\n\n";
        if (!empty($this->state)) {
            switch ($this->state) {
                case self::RESOLVED:
                    $text .= "State: Resolved ✅" . "\n\n";
                    break;
                case self::FIRE:
                    $text .= "State: Fire 🔥" . "\n\n";
                    break;
            }
        }

        if ($alertRule->queryType == AlertRule::DYNAMIC_QUERY_TYPE){
            $text .= "AlertName: " . $alertRule->dataSourceAlertName . "\n\n";
        }else{
            $needLabelArray[] = "alertname";
        }
        if (!empty($this->alerts)) {
            foreach ($this->alerts as $alert) {
                if (empty($alert['skylogsStatus']) || $alert['skylogsStatus'] == self::FIRE) {
                    $text .= "Fire 🔥" . "\n";
                }else{
                    $text .= "Resolved ✅" . "\n";

                }

                $text .= "Data Source: " . $alert['dataSourceName'] . "\n";
                if (!empty($alert['labels']))
                    foreach ($needLabelArray as $label) {
                        if (!empty($alert['labels'][$label])) {
                            $text .= "$label : " . $alert['labels'][$label] . "\n";
                        }
                    }
                if (!empty($alert['annotations']))
                    foreach ($needLabelAnotArray as $label) {
                        if (!empty($alert['annotations'][$label])) {
                            $text .= "$label : " . $alert['annotations'][$label] . "\n";
                        }
                    }
                $text .= "\n************\n\n";
            }
        }



        $text .= "date: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }

}
