<?php

namespace App\Services;

use App\Enums\AlertRuleType;
use App\Enums\ClusterType;
use App\Jobs\SendNotifyJob;
use App\Models\AlertInstance;
use App\Models\AlertRule;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\Cache;

class ClusterService
{
    private ClusterType $clusterType;

    public function __construct(
        #[Config("app.clusterType")]
        $clusterType
    )
    {
        $this->clusterType = ClusterType::from($clusterType);
    }

    public function type():ClusterType
    {
        return $this->clusterType;
    }

    public function clusterByToken($token):ClusterType
    {
        return $this->clusterType;
    }


}
