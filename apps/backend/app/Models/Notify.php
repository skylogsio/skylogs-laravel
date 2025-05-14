<?php

namespace App\Models;

use App\Interfaces\Messageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;
use Morilog\Jalali\Jalalian;

class Notify extends BaseModel implements Messageable
{

    public $timestamps = true;
    public static $title = "Notify";
    public static $KEY = "notify";

    protected $guarded = ['id', '_id',];

    protected $casts = [];

    public const STATUS_CREATED = 1;
    public const STATUS_RUNNING = 2;
    public const STATUS_DONE = 3;
    public const STATUS_FAIL = 4;
    public const STATUS_SILENT = 5;


    public function alertRule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, "alertRuleId", "_id");
    }


    public function telegramMessage()
    {
        return $this->messages['telegramMessage'];
    }

    public function teamsMessage()
    {
        return $this->messages['teamsMessage'];
    }

    public function emailMessage()
    {
        return $this->messages['emailMessage'];
    }

    public function smsMessage()
    {
        return $this->messages['smsMessage'];
    }

    public function callMessage()
    {
        return $this->messages['callMessage'];
    }
}
