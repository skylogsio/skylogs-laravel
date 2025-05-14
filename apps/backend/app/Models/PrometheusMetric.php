<?php

namespace App\Models;

use App\Interfaces\Messageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;
use Morilog\Jalali\Jalalian;

class PrometheusMetric extends BaseModel
{

    public $timestamps = true;
    public static $title = "Prometheus Metric";
    public static $KEY = "prometheus_metric";

    protected $guarded = ['id', '_id',];


    public function prometheusInstance(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, "instance_id", "_id");
    }


}
