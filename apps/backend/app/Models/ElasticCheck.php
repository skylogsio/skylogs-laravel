<?php

namespace App\Models;

use App\Interfaces\Messageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;
use Morilog\Jalali\Jalalian;

class ElasticCheck extends Model implements Messageable
{

    public $timestamps = true;
    public static $title = "Elastic Check";
    public static $KEY = "elastic_check";

    protected $guarded = ['id', '_id',];

    public const CONDITION_TYPE_GREATER_OR_EQUAL = "greaterOrEqual";
    public const CONDITION_TYPE_LESS_OR_EQUAL = "lessOrEqual";

    public const RESOLVED = 1;
    public const FIRE = 2;
    public const NOTIFICATION = 3;


    public function alertRule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, "alert_rule_id", "_id");
    }


    public function telegramMessage(): string
    {

        $text = $this->alertRule->alertname . "\n\n";
        if (!empty($this->state)) {
            switch ($this->state) {
                case self::RESOLVED:
                    $text .= "State: Resolved ✅". "\n\n";
                    break;
                case self::FIRE:
                    $text .= "State: Fire 🔥". "\n\n";
                    break;
            }
        }
        if (!empty($this->dataview_name)) {
            $text .= "Data View: " . $this->dataview_name . "\n\n";
        }

        $text .= "date: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }

    public function teamsMessage(): string
    {

        $text = $this->alertRule->alertname . "\n\n";
        if (!empty($this->state)) {
            switch ($this->state) {
                case self::RESOLVED:
                    $text .= "State: Resolved ✅". "\n\n";
                    break;
                case self::FIRE:
                    $text .= "State: Fire 🔥". "\n\n";
                    break;
            }
        }
        if (!empty($this->dataview_name)) {
            $text .= "Data View: " . $this->dataview_name . "\n\n";
        }

        $text .= "date: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }
    public function emailMessage(): string
    {

        $text = $this->alertRule->alertname . "\n\n";
        if (!empty($this->state)) {
            switch ($this->state) {
                case self::RESOLVED:
                    $text .= "State: Resolved ✅". "\n\n";
                    break;
                case self::FIRE:
                    $text .= "State: Fire 🔥". "\n\n";
                    break;
            }
        }
        if (!empty($this->dataview_name)) {
            $text .= "Data View: " . $this->dataview_name . "\n\n";
        }

        $text .= "date: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }

    public function smsMessage(): string
    {

        $text = $this->alertRule->alertname . "\n\n";
        if (!empty($this->state)) {
            switch ($this->state) {
                case self::RESOLVED:
                    $text .= "State: Resolved ✅". "\n\n";
                    break;
                case self::FIRE:
                    $text .= "State: Fire 🔥". "\n\n";
                    break;
            }
        }
        if (!empty($this->dataview_name)) {
            $text .= "Data View: " . $this->dataview_name . "\n\n";
        }

        $text .= "date: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }

    public function callMessage(): string
    {

        $text = $this->alertRule->alertname  . "\n\n";
        if (!empty($this->state)) {
            switch ($this->state) {
                case self::RESOLVED:
                    $text .= "State: Resolved ✅". "\n\n";
                    break;
                case self::FIRE:
                    $text .= "State: Fire 🔥". "\n\n";
                    break;
            }
        }
        if (!empty($this->dataview_name)) {
            $text .= "Data View: " . $this->dataview_name . "\n\n";
        }

        $text .= "date: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }

}
