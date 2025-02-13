<?php

namespace App\Models;

use App\interfaces\Messageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;
use Morilog\Jalali\Jalalian;

class HealthCheck extends Model implements Messageable
{

    public $timestamps = true;
    public static $title = "Health Check";
    public static $KEY = "health_check";

    protected $guarded = ['id', '_id',];

    public const DOWN = 1;
    public const UP = 2;


    public function alertRule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, "alert_rule_id", "_id");
    }


    public function telegramMessage(): string
    {

        $text = $this->alertRule->alertname . "\n\n";
        if (!empty($this->state)) {
            switch ($this->state) {
                case self::UP:
                    $text .= "State: UP ✅" . "\n\n";
                    break;
                case self::DOWN:
                    $text .= "State: DOWN 🔥" . "\n\n";
                    break;
            }
        }


        $text .= "date: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }

    public function teamsMessage(): string
    {

        $text = $this->alertRule->alertname . "\n\n";
        if (!empty($this->state)) {
            switch ($this->state) {
                case self::UP:
                    $text .= "State: UP ✅" . "\n\n";
                    break;
                case self::DOWN:
                    $text .= "State: DOWN 🔥" . "\n\n";
                    break;
            }
        }


        $text .= "date: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }
    public function emailMessage(): string
    {

        $text = $this->alertRule->alertname . "\n\n";
        if (!empty($this->state)) {
            switch ($this->state) {
                case self::UP:
                    $text .= "State: UP ✅" . "\n\n";
                    break;
                case self::DOWN:
                    $text .= "State: DOWN 🔥" . "\n\n";
                    break;
            }
        }


        $text .= "date: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }

    public function smsMessage(): string
    {

        $text = $this->alertRule->alertname . "\n\n";
        if (!empty($this->state)) {
            switch ($this->state) {
                case self::UP:
                    $text .= "State: UP ✅" . "\n\n";
                    break;
                case self::DOWN:
                    $text .= "State: DOWN 🔥" . "\n\n";
                    break;
            }
        }


        $text .= "date: " . Jalalian::now()->format("Y/m/d");
        return $text;
    }

    public function callMessage(): string
    {

        $text = $this->alertRule->alertname . "\n\n";
        if (!empty($this->state)) {
            switch ($this->state) {
                case self::UP:
                    $text .= "State: UP ✅" . "\n\n";
                    break;
                case self::DOWN:
                    $text .= "State: DOWN 🔥" . "\n\n";
                    break;
            }
        }


        $text .= "date: " . Jalalian::now()->format("Y/m/d");

        return $text;
    }

}
