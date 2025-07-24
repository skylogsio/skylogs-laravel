<?php

namespace App\Models;

use App\Services\SkylogsInstanceService;

class SkylogsInstance extends BaseModel
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
