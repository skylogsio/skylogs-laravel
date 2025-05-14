<?php

namespace App\Models;

use App\Interfaces\Messageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;
use Morilog\Jalali\Jalalian;

class AlertInstance extends BaseModel implements Messageable
{

    public $timestamps = true;
    public static $title = "Alert Instance";
    public static $KEY = "alerts";

    protected $guarded = ['id', '_id',];


    public const RESOLVED = 1;
    public const FIRE = 2;
    public const NOTIFICATION = 3;


    public function alertRule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, "alertRuleId", "id");
    }

    public function history(): BelongsTo
    {
        return $this->belongsTo(ApiAlertHistory::class, "historyId");
    }

    public function UpdatedAtString()
    {
        return Jalalian::fromCarbon($this->updatedAt)->format('Y/m/d H:i:s');
    }

    public function createHistory()
    {
        $model = new ApiAlertHistory();
        $model->alertRuleId = $this->alertRuleId;
        $model->alertRuleName = $this->alertRuleName;
        $model->instance = $this->instance;
        $model->description = $this->description;
        $model->summary = $this->summary;
        $model->state = $this->state;


        $model->save();

        $this->historyId = $model->_id;
        $this->save();

        return $model;
    }

    public function createStatusHistory($history)
    {
        $model = new ApiAlertStatusHistory();
        $model->alertRuleId = $this->alertRuleId;
        $model->instance = $this->instance;
        $model->description = $this->description;
        $model->summary = $this->summary;
        $alertRule = $this->alertRule;
        $instances = $alertRule->apiInstances();
        if ($instances) {
            $isResolved = true;
            $alertFireInstances = [];
            foreach ($instances as $instance) {
                if ($instance->instance == self::FIRE) {
                    $isResolved = false;
                    $alertFireInstances[] = $history->toArray();
                }
            }

            if ($isResolved) {
                $model->state = self::RESOLVED;
            } else {
                $model->state = self::FIRE;
                $model->countAlerts = count($alertFireInstances);
                $model->firedInstances = $alertFireInstances;
            }

        } else
            $model->state = self::RESOLVED;

        $model->alertRuleId = $this->alertRuleId;

        $model->save();

    }

    public function telegramMessage(): string
    {
        $text = $this->alertRuleName;

        $text .= match ($this->state) {
            self::FIRE => "\nState: Fire 🔥",
            self::RESOLVED => "\nState: Resolve ✅",
            self::NOTIFICATION => "\nState: Notification 📢",
            default => "\nstate: Unknown",
        };

        if (!empty($this->instance))
            $text .= "\nInstance: " . $this->instance;

        if (!empty($this->description))
            $text .= "\nDescription: " . $this->description;

        $text .= "\nDate: " . $this->updatedAtString();
//        $text .= $this->description;

        return $text;
    }

    public function smsMessage(): string
    {
        $text = $this->alertRuleName;

        $text .= match ($this->state) {
            self::FIRE => "\nState: Fire 🔥",
            self::RESOLVED => "\nState: Resolve ✅",
            self::NOTIFICATION => "\nState: Notification 📢",
            default => "\nstate: Unknown",
        };

        if (!empty($this->instance))
            $text .= "\nInstance: " . $this->instance;

        if (!empty($this->description))
            $text .= "\nDescription: " . $this->description;

        $text .= "\nDate: " . $this->updatedAtString();

        return $text;
    }

    public function callMessage(): string
    {
        $text = $this->alertRuleName;

        $text .= match ($this->state) {
            self::FIRE => "\nstate: Fire",
            self::RESOLVED => "\nstate: Resolve",
            self::NOTIFICATION => "\nState: Notification 📢",
            default => "\nstate: Unknown",
        };

        if (!empty($this->instance))
            $text .= "\nInstance: " . $this->instance;
        if (!empty($this->description))
            $text .= "\ndescription: " . $this->description;
        $text .= "\ndate: " . Jalalian::now()->format("Y/m/d");
        return $text;
    }

    public function teamsMessage()
    {
        $text = $this->alertname;

        $text .= match ($this->state) {
            self::FIRE => "\nState: Fire 🔥",
            self::RESOLVED => "\nState: Resolve ✅",
            self::NOTIFICATION => "\nState: Notification 📢",
            default => "\nstate: Unknown",
        };

        if (!empty($this->instance))
            $text .= "\nInstance: " . $this->instance;

        if (!empty($this->description))
            $text .= "\nDescription: " . $this->description;

        $text .= "\nDate: " . $this->updatedAtString();

        return $text;
    }

    public function emailMessage()
    {
        $text = $this->alertRuleName;

        $text .= match ($this->state) {
            self::FIRE => "\nState: Fire 🔥",
            self::RESOLVED => "\nState: Resolve ✅",
            self::NOTIFICATION => "\nState: Notification 📢",
            default => "\nstate: Unknown",
        };

        if (!empty($this->instance))
            $text .= "\nInstance: " . $this->instance;

        if (!empty($this->description))
            $text .= "\nDescription: " . $this->description;

        $text .= "\nDate: " . $this->updatedAtString();

        return $text;
    }

}
