<?php

namespace App\Models;

use App\Helpers\Constants;
use App\Interfaces\Messageable;
use Morilog\Jalali\Jalalian;

class ZabbixWebhookAlert extends BaseModel implements Messageable
{

    public $timestamps = true;
    public static $title = "Zabbix Alert Webhook";
    public static $KEY = "zabbix_alert_webhook";

    protected $guarded = ['id', '_id',];

    public function alertRule()
    {
        return AlertRule::where("type", Constants::ZABBIX)->where('alertname', $this->action_name)->first();
    }
    public function CustomSave($jsonWebhook)
    {
        try {

            foreach ($jsonWebhook as $key => $value) {
                $this->$key = $value;

            }

            $alert = AlertRule::where("alertname", $this->action_name)->first();
            if ($alert) {
                $this->alertRuleId = $alert->_id;
//                $alert->state = $this->action;
                $alert->notifyAt = time();
                $alert->save();
            }
        } catch (\Exception $e) {
        }
        return $this->save();
    }

    public function defaultMessage(): string
    {
        $text = $this->action_name;

        $alert = AlertRule::where("alertname", $this->action_name)->first();

//        if (!empty($alert->state)) {
//            switch ($alert->state) {
//                case AlertRule::RESOlVED:
//                    $text .= "\nSeverity: Resolved ✅";
//                    break;
//                case AlertRule::WARNING:
//                    $text .= "\nSeverity: Warning ⚠️";
//                    break;
//                case AlertRule::CRITICAL:
//                    $text .= "\nSeverity: Critical 🔥";
//                    break;
//            }
//        }

//        $text .= "\nlevel: " . $this->action;
        $needArray = ["event_source", "event_update_status", "event_value", "Message", "Subject", "tags"];

        $text .= "\nSubject: " . $this->alert_subject;
        $text .= "\nMessage: " . $this->alert_message;
        if (!empty($this->event_name))
            $text .= "\nEvent Name: " . $this->event_name;
        if (!empty($this->event_severity))
            $text .= "\nEvent Severity: " . $this->event_severity;
        if (!empty($this->host_ip))
            $text .= "\nHost ip: " . $this->host_ip;
        if (!empty($this->host_name))
            $text .= "\nHost name: " . $this->host_name;



        $text .= "\nDate: " . Jalalian::now()->format("Y/m/d");
//        $text .= $this->description;

        return $text;
    }

    public function telegramMessage()
    {
        return $this->defaultMessage();
    }
    public function matterMostMessage()
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

    public function smsMessage(): string
    {
        return $this->defaultMessage();
    }

    public function callMessage(): string
    {
        return $this->defaultMessage();
    }
}
