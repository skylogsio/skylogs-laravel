<?php

namespace App\Models;

use App\interfaces\Messageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;
use Morilog\Jalali\Jalalian;

class GrafanaInstance extends Model
{

    public $timestamps = true;
    public static $title = "Grafana Instance";
    public static $KEY = "grafana_instance";

    protected $guarded = ['id', '_id',];




}
