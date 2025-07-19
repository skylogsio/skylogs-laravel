<?php

namespace App\Models;

class GrafanaInstance extends BaseModel
{

    public $timestamps = true;
    public static $title = "Grafana Instance";
    public static $KEY = "grafana_instance";

    protected $guarded = ['id', '_id',];




}
