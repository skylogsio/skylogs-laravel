<?php

namespace App\Models;

use App\Interfaces\Messageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;
use Morilog\Jalali\Jalalian;

class GroupAlertRule extends Model
{

    public $timestamps = true;
    public static $title = "Group";
    public static $KEY = "group";

    protected $guarded = ['id', '_id',];

//    protected $casts = [];

    public const STATUS_CREATED = 1;
    public const STATUS_RUNNING = 2;
    public const STATUS_DONE = 3;
    public const STATUS_FAIL = 4;
    public const STATUS_SILENT = 5;

}
