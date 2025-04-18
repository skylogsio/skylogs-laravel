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

class MetabaseWebhookAlert extends Model implements Messageable
{

    public $timestamps = true;
    public static $title = "Metabase Alert Webhook";
    public static $KEY = "metabase_alert_webhook";

    protected $guarded = ['id', '_id',];

    public function alertRule()
    {
        return AlertRule::where("type", Constants::METABASE)->where('alertname', $this->alert_name)->first();
    }

    public function CustomSave($jsonWebhook)
    {
        try {
            $dataArray = $jsonWebhook['data'];
            $this->data = $dataArray;

            $this->type = $jsonWebhook['type'];
            $this->alert_id = $jsonWebhook['alert_id'];
            $this->alert_creator_id = $jsonWebhook['alert_creator_id'];
            $this->alert_creator_name = $jsonWebhook['alert_creator_name'];
            $this->question_name = $dataArray['question_name'];
            $this->alert_name = $dataArray['question_name'];
            $this->question_url = $dataArray['question_url'];

            $alert = AlertRule::where("alertname", $this->alert_name)->first();
            if ($alert) {
                $this->alert_rule_id = $alert->_id;
                $alert->state = AlertRule::UNKNOWN;
                $alert->notify_at = time();
                $alert->save();
            }


        } catch (\Exception $e) {

        }
        return $this->save();
    }

    public function telegramMessage(): string
    {
        $text = $this->alert_name;

        $text .= "\n⚠️ TRIGGERED Metabase Alert ";

        $text .= "\nUrl: " . $this->question_url;
        $text .= "\nDate: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }

    public function teamsMessage(): string
    {
        $text = $this->alert_name;

        $text .= "\n⚠️ TRIGGERED Metabase Alert ";

        $text .= "\nUrl: " . $this->question_url;
        $text .= "\nDate: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }
    public function emailMessage(): string
    {
        $text = $this->alert_name;

        $text .= "\n⚠️ TRIGGERED Metabase Alert ";

        $text .= "\nUrl: " . $this->question_url;
        $text .= "\nDate: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }

    public function smsMessage(): string
    {
        $text = $this->alert_name;

        $text .= "\n⚠️ TRIGGERED Metabase Alert ";

        $text .= "\nUrl: " . $this->question_url;
        $text .= "\nDate: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }

    public function callMessage(): string
    {
        $text = $this->alert_name;

        $text .= "\n⚠️ TRIGGERED Metabase Alert ";

        $text .= "\nUrl: " . $this->question_url;
        $text .= "\nDate: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }
}
