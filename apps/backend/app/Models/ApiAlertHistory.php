<?php

namespace App\Models;

use App\interfaces\Messageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;
use Morilog\Jalali\Jalalian;

class ApiAlertHistory extends Model
{

    public $timestamps = true;
    public static $title = "Api Alert History";
    public static $KEY = "history";

    protected $guarded = ['id', '_id',];

    public const RESOLVED = 1;
    public const FIRE = 2;
    public const NOTIFICATION = 3;


    public function alertRule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class,"alertname","alertname");
    }



}
