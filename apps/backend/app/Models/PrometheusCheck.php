<?php

namespace App\Models;

use App\interfaces\Messageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;
use Morilog\Jalali\Jalalian;

class PrometheusCheck extends Model implements Messageable
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
        return $this->belongsTo(AlertRule::class, "alert_rule_id", "_id");
    }

    public function getAlertRulePrometheus(): ?AlertRulePrometheus
    {

        $alertRulePrometheus = AlertRulePrometheus::where("name", $this['instance'] . "-" . $this['labels']['alertname'])->first();

        if ($alertRulePrometheus) {
            return $alertRulePrometheus;
        } else {
            return null;
        }
    }

    public function createHistory()
    {

        PrometheusHistory::create(
            [
                "alert_rule_id" => $this->alert_rule_id,
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
                    "alert_rule_id" => $this->alert_rule_id,
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

        $text = $alertRule->alertname . "\n\n";
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
            $text .= "AlertName: " . $alertRule->prometheus_alertname . "\n\n";
        }else{
            $needLabelArray[] = "alertname";
        }

        if (!empty($this->alerts)) {
            foreach ($this->alerts as $alert) {
                if (empty($alert['skylogs_status']) || $alert['skylogs_status'] == self::FIRE) {
                    $text .= "Fire 🔥" . "\n";
                }else{
                    $text .= "Resolved ✅" . "\n";

                }

                $text .= "Prometheus Instance: " . $alert['instance'] . "\n";
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

        $text = $alertRule->alertname . "\n\n";
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
            $text .= "AlertName: " . $alertRule->prometheus_alertname . "\n\n";
        }else{
            $needLabelArray[] = "alertname";
        }
        if (!empty($this->alerts) ) {
            foreach ($this->alerts as $alert) {
                if (empty($alert['skylogs_status']) || $alert['skylogs_status'] == self::FIRE) {
                    $text .= "Fire 🔥" . "\n";
                }else{
                    $text .= "Resolved ✅" . "\n";

                }

                $text .= "Prometheus Instance: " . $alert['instance'] . "\n";
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

        $text = $alertRule->alertname . "\n\n";
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
            $text .= "AlertName: " . $alertRule->prometheus_alertname . "\n\n";
        }else{
            $needLabelArray[] = "alertname";
        }
        if (!empty($this->alerts) ) {
            foreach ($this->alerts as $alert) {
                if (empty($alert['skylogs_status']) || $alert['skylogs_status'] == self::FIRE) {
                    $text .= "Fire 🔥" . "\n";
                }else{
                    $text .= "Resolved ✅" . "\n";

                }

                $text .= "Prometheus Instance: " . $alert['instance'] . "\n";
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

        $text = $alertRule->alertname . "\n\n";
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
            $text .= "AlertName: " . $alertRule->prometheus_alertname . "\n\n";
        }else{
            $needLabelArray[] = "alertname";
        }
        if (!empty($this->alerts)) {
            foreach ($this->alerts as $alert) {
                if (empty($alert['skylogs_status']) || $alert['skylogs_status'] == self::FIRE) {
                    $text .= "Fire 🔥" . "\n";
                }else{
                    $text .= "Resolved ✅" . "\n";

                }

                $text .= "Prometheus Instance: " . $alert['instance'] . "\n";
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

        $text = $alertRule->alertname . "\n\n";
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
            $text .= "AlertName: " . $alertRule->prometheus_alertname . "\n\n";
        }else{
            $needLabelArray[] = "alertname";
        }
        if (!empty($this->alerts)) {
            foreach ($this->alerts as $alert) {
                if (empty($alert['skylogs_status']) || $alert['skylogs_status'] == self::FIRE) {
                    $text .= "Fire 🔥" . "\n";
                }else{
                    $text .= "Resolved ✅" . "\n";

                }

                $text .= "Prometheus Instance: " . $alert['instance'] . "\n";
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

    public function isPrometheusInstanceOk()
    {
        // TODO new approach (multi instance) may not first instance
        try {
            if (!empty($this->alerts)) {
                $proInstance = PrometheusInstance::where("name", $this->alerts[0]["instance"])->first();
            } elseif (!empty($this->instance)) {
                $proInstance = PrometheusInstance::where("name", $this->instance)->first();
            }
            if ($proInstance)
                return $proInstance->isOk();
            else
                return true;

        } catch (\Exception $e) {
            return false;
        }

    }

}
