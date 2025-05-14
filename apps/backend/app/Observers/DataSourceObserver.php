<?php

namespace App\Observers;

use App\Models\DataSource\DataSource;
use App\Services\DataSourceService;

class DataSourceObserver {

    public function created(DataSource $dataSource): void {
        DataSourceService::FlushCache();
    }

    public function updated(DataSource $dataSource): void {
        DataSourceService::FlushCache();
    }

    public function deleted(DataSource $dataSource): void {
        DataSourceService::FlushCache();
    }

    public function restored(DataSource $dataSource): void {
        DataSourceService::FlushCache();
    }

    public function forceDeleted(DataSource $dataSource): void {
        DataSourceService::FlushCache();
    }

}
