<?php

namespace App\Models;

use App\Interfaces\Messageable;
use MongoDB\Laravel\Relations\BelongsTo;
use Morilog\Jalali\Jalalian;

class GrafanaWebhookAlert extends BaseModel implements Messageable
{

    public $timestamps = true;
    public static $title = "Grafana Alert Webhook";
    public static $KEY = "grafana_alert_webhook";

    protected $guarded = ['id', '_id',];
    public const FIRING = "firing";
    public const RESOLVED = "resolved";


    public function customSave($array)
    {
        try {

            foreach ($array as $item => $value) {
                $this->$item = $value;
            }

            $alert = $this->alertRule;

            if ($alert) {
                if ($this->status == self::RESOLVED)
                    $alert->state = AlertRule::RESOlVED;
                elseif ($this->status == self::FIRING)
                    $alert->state = AlertRule::CRITICAL;
                $alert->save();
            }
        } catch (\Exception $e) {

        }
        return $this->save();
    }


    public function alertRule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, "alertRuleId", "_id");
    }

    public function defaultMessage(): string
    {

        $needLabelAnotArray = ["summary", "description"];

        $alert = $this->alertRule;

        $text = $alert->name . "\n\n";

        if (!empty($alert->state)) {
            switch ($alert->state) {
                case AlertRule::RESOlVED:
                    $text .= "State: Resolved ✅" . "\n\n";
                    break;
                case AlertRule::CRITICAL:
                    $text .= "State: Fire 🔥" . "\n\n";
                    break;
            }
        }


        $text .= "Data Source: " . $this->dataSourceName . "\n\n";


        if (!empty($this->alerts)) {
            foreach ($this->alerts as $alert) {
//                $text .= "Grafana Instance: " . $alert['dataSourceName'] . "\n";

                if (!empty($alert['labels']))
                    foreach ($alert['labels'] as $label => $labelValue) {
                        $text .= "$label : $labelValue\n";
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


        $text .= "Date: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }

    public function telegram()
    {
        return $this->defaultMessage();
    }

    public function matterMostMessage()
    {
        return $this->defaultMessage();
    }

    public function smsMessage(): string
    {
        return $this->defaultMessage();
    }

    public function callMessage(): string
    {
        return $this->defaultMessage();
    }

    public function teamsMessage(): string
    {
        return $this->defaultMessage();
    }

    public function emailMessage(): string
    {
        return $this->defaultMessage();
    }
}
