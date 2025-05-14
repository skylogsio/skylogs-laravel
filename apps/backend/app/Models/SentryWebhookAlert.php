<?php

namespace App\Models;

use App\Helpers\Utilities;
use App\Interfaces\Messageable;
use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;
use Morilog\Jalali\Jalalian;

class SentryWebhookAlert extends BaseModel implements Messageable
{

    public $timestamps = true;
    public static $title = "Sentry Alert Webhook";
    public static $KEY = "sentry_alert_webhook";

    protected $guarded = ['id', '_id',];

    public function alertRule()
    {
        return  AlertRule::where("type", Constants::SENTRY)->where('alertname', $this->alert_name)->first();
    }
    public function CustomSave($jsonWebhook)
    {
        try {
            $dataArray = $jsonWebhook['data'];
            Utilities::removeEmptyKeys($dataArray);
            $this->data = $dataArray;

            if(!empty($dataArray['metric_alert'])){
                $this->project_name = $jsonWebhook['data']['metric_alert']['projects'][0] ?? "";
                $this->action = $jsonWebhook['action'];

                $this->message = $jsonWebhook['data']['description_text'] ?? "";
                $this->description = $jsonWebhook['data']['description_text'] ?? "";
                $this->url = $jsonWebhook['data']['web_url'] ?? "";
                $this->alert_name = $jsonWebhook['data']['metric_alert']['alert_rule']['name'] ?? "";
                $alert = AlertRule::where("alertname",$this->alert_name)->first();
                if($alert){
                    $this->alertRuleId = $alert->_id;
                    $alert->state = $this->action;
                    $alert->notify_at = time();
                    $alert->save();
                }
            }else{

                $this->action = $jsonWebhook['action'];

                $this->message = $jsonWebhook['data']['event']['message'] ?? "";
                $this->description = $jsonWebhook['data']['event']['message'] ?? "";
                $this->url = $jsonWebhook['data']['event']['web_url'] ?? "";
                $this->alert_name = $jsonWebhook['data']['triggered_rule'] ?? "";
                $alert = AlertRule::where("alertname",$this->alert_name)->first();
                if($alert){
                    $this->alertRuleId = $alert->_id;
                    $alert->state = $this->action;
                    $alert->notify_at = time();
                    $alert->save();
                }
            }


        } catch (\Exception $e) {

        }
        return $this->save();
    }

    public function telegramMessage(): string
    {
        $text = $this->alert_name;

        $alert = AlertRule::where("alertname",$this->alert_name)->first();

        if (!empty($alert->state)) {
            switch ($alert->state) {
                case AlertRule::RESOlVED:
                    $text .= "\nSeverity: Resolved ✅";
                    break;
                case AlertRule::WARNING:
                    $text .= "\nSeverity: Warning ⚠️";
                    break;
                case AlertRule::CRITICAL:
                    $text .= "\nSeverity: Critical 🔥";
                    break;
                case AlertRule::TRIGGERED:
                    $text .= "\nTRIGGERED Alert";
                    break;

            }
        }

//        $text .= "\nlevel: " . $this->action;
        $text .= "\nDescription: " . $this->description;
        $text .= "\nUrl: " . $this->url;
        $text .= "\nDate: " . Jalalian::now()->format("Y/m/d");
//        $text .= $this->description;

        return $text;
    }

    public function teamsMessage(): string
    {
        $text = $this->alert_name;

        $alert = AlertRule::where("alertname",$this->alert_name)->first();

        if (!empty($alert->state)) {
            switch ($alert->state) {
                case AlertRule::RESOlVED:
                    $text .= "\nSeverity: Resolved ✅";
                    break;
                case AlertRule::WARNING:
                    $text .= "\nSeverity: Warning ⚠️";
                    break;
                case AlertRule::CRITICAL:
                    $text .= "\nSeverity: Critical 🔥";
                    break;
                case AlertRule::TRIGGERED:
                    $text .= "\nTRIGGERED Alert";
                    break;

            }
        }

//        $text .= "\nlevel: " . $this->action;
        $text .= "\nDescription: " . $this->description;
        $text .= "\nUrl: " . $this->url;
        $text .= "\nDate: " . Jalalian::now()->format("Y/m/d");
//        $text .= $this->description;

        return $text;
    }
    public function emailMessage(): string
    {
        $text = $this->alert_name;

        $alert = AlertRule::where("alertname",$this->alert_name)->first();

        if (!empty($alert->state)) {
            switch ($alert->state) {
                case AlertRule::RESOlVED:
                    $text .= "\nSeverity: Resolved ✅";
                    break;
                case AlertRule::WARNING:
                    $text .= "\nSeverity: Warning ⚠️";
                    break;
                case AlertRule::CRITICAL:
                    $text .= "\nSeverity: Critical 🔥";
                    break;
                case AlertRule::TRIGGERED:
                    $text .= "\nTRIGGERED Alert";
                    break;

            }
        }

//        $text .= "\nlevel: " . $this->action;
        $text .= "\nDescription: " . $this->description;
        $text .= "\nUrl: " . $this->url;
        $text .= "\nDate: " . Jalalian::now()->format("Y/m/d");
//        $text .= $this->description;

        return $text;
    }

    public function smsMessage(): string
    {
        $text = $this->alert_name;

        $alert = AlertRule::where("alertname",$this->alert_name)->first();

        if (!empty($alert->state)) {
            switch ($alert->state) {
                case AlertRule::RESOlVED:
                    $text .= "\nSeverity: Resolved ✅";
                    break;
                case AlertRule::WARNING:
                    $text .= "\nSeverity: Warning ⚠️";
                    break;
                case AlertRule::CRITICAL:
                    $text .= "\nSeverity: Critical 🔥";
                    break;
                case AlertRule::TRIGGERED:
                    $text .= "\nTRIGGERED Alert";
                    break;
            }
        }


//        $text .= "\nlevel: " . $this->action;
        $text .= "\nDescription: " . $this->description;
        $text .= "\nUrl: " . $this->url;
        $text .= "\nDate: " . Jalalian::now()->format("Y/m/d");
//        $text .= $this->description;

        return $text;
    }

    public function callMessage(): string
    {
        $text = $this->alert_name;
        $text .= "\nlevel: " . $this->action;
        $text .= "\ndescription: " . $this->description;
        $text .= "\nurl: " . $this->url;
        $text .= "\ndate: " . Jalalian::now()->format("Y/m/d");
//        $text .= $this->description;

        return $text;
    }
}
