<?php

namespace App\Models;

use App\interfaces\Messageable;
use App\Services\PrometheusInstanceService;
use App\Services\SkylogsInstanceService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;
use Morilog\Jalali\Jalalian;

class SkylogsInstance extends Model
{

    public $timestamps = true;
    public static $title = "Skylogs Instance";
    public static $KEY = "skylogs_instance";

    public const STATE_FIRING = "firing";

    public const DOWN = 1;
    public const UP = 2;


    protected $guarded = ['id', '_id',];

    public function isOk(){
        return SkylogsInstanceService::getHealthCheck($this);
    }

    public function isLeader(){
        return SkylogsInstanceService::isLeader($this);
    }
    public function setLeader(){
        SkylogsInstanceService::SetServerPriority($this);
    }

    public function getBaseUrl()
    {
        return \Str::startsWith($this->url,"http") ? $this->url : "http://".$this->url;
    }

    public function getHealthUrl(){
        return $this->getBaseUrl() . "/api/health";
    }
    public function getPingUrl(){
        return $this->getBaseUrl() . "/api/leaderPing";
    }

}
