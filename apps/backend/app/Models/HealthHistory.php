<?php

namespace App\Models;

use App\Interfaces\Messageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;
use Morilog\Jalali\Jalalian;

class HealthHistory extends BaseModel
{

    public $timestamps = true;
    public static $title = "Health History";
    public static $KEY = "health_history";

    protected $guarded = ['id', '_id',];

    public const DOWN = 1;
    public const UP = 2;


    public function alertRule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, "alertRuleId", "_id");
    }

}
