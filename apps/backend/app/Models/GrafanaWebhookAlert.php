<?php

namespace App\Models;

use App\Interfaces\Messageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;
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

    public function telegramMessage(): string
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


        if ($alert->queryType == AlertRule::DYNAMIC_QUERY_TYPE) {
            $text .= "AlertName: " . $alert->dataSourceAlertName . "\n\n";
        }

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

    public function smsMessage(): string
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


        if ($alert->queryType == AlertRule::DYNAMIC_QUERY_TYPE) {
            $text .= "AlertName: " . $alert->dataSourceAlertName . "\n\n";
        }

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

    public function callMessage(): string
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


        if ($alert->queryType == AlertRule::DYNAMIC_QUERY_TYPE) {
            $text .= "AlertName: " . $alert->dataSourceAlertName . "\n\n";
        }

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

    public function teamsMessage()
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


        if ($alert->queryType == AlertRule::DYNAMIC_QUERY_TYPE) {
            $text .= "AlertName: " . $alert->dataSourceAlertName . "\n\n";
        }

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

    public function emailMessage()
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


        if ($alert->queryType == AlertRule::DYNAMIC_QUERY_TYPE) {
            $text .= "AlertName: " . $alert->dataSourceAlertName . "\n\n";
        }

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
}
