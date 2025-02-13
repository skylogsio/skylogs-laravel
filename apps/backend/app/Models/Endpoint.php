<?php

namespace App\Models;

use App\interfaces\Messageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class Endpoint extends Model
{

    public $timestamps = true;
    public static $title = "Endpoint";
    public static $KEY = "endpoint";

    public const SMS ="sms";
    public const CALL ="call";
    public const TELEGRAM ="telegram";
    public const TEAMS ="teams";

    protected $guarded = ['id','_id',];


    public static function boot() {
        parent::boot();

        static::deleting(function ($endpoint) {
            // Delete the post from the user's posts
//            AlertRule::where()->endpoints()->where('_id', $endpoint->_id)->delete();
        });
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function alertRules() {
        return $this->belongsToMany(AlertRule::class);
    }

    public static array $types = [
        "sms" => "SMS",
        "email" => "Email",
        "call" => "Call",
        "telegram" => "Telegram",
        "teams" => "Teams",
    ];

}
