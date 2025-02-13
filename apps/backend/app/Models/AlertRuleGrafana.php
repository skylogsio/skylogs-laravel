<?php

namespace App\Models;

use App\interfaces\Messageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;

class AlertRuleGrafana extends Model implements Messageable
{

    public $timestamps = true;
    public static $title = "Alert Rule Prometheus";
    public static $KEY = "alerts";

    protected $guarded = ['id','_id',];


    public static $types = [
        "api" => "Api",
        "sentry" => "Sentry",
        "health" => "Health",
    ];

    public function telegramMessage()
    {
        // TODO: Implement telegramMessage() method.
    }
    public function emailMessage()
    {
        // TODO: Implement telegramMessage() method.
    }
    public function teamsMessage()
    {
        // TODO: Implement teamsMessage() method.
    }

    public function smsMessage()
    {
        // TODO: Implement smsMessage() method.
    }

    public function callMessage()
    {
        // TODO: Implement callMessage() method.
    }
}
