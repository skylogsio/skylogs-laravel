<?php

namespace App\Observers;

use App\Models\Endpoint;
use App\Services\DataSourceService;
use App\Services\EndpointService;

class EndpointObserver {

    public function created(Endpoint $endpoint): void {
        EndpointService::FlushCache();
    }

    public function updated(Endpoint $endpoint): void {
        EndpointService::FlushCache();
    }

    public function deleted(Endpoint $endpoint): void {
        EndpointService::RefreshAlertRuleEndpoints($endpoint);
        EndpointService::FlushCache();
    }

    public function restored(Endpoint $endpoint): void {
        EndpointService::FlushCache();
    }

    public function forceDeleted(Endpoint $endpoint): void {
        EndpointService::FlushCache();
    }

}
