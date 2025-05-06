<?php

namespace App\Observers;

use App\Models\AlertRule;
use App\Services\DataSourceService;
use App\Services\AlertRuleService;

class AlertRuleObserver {

    public function created(AlertRule $alertRule): void {
        AlertRuleService::FlushCache();
    }

    public function updated(AlertRule $alertRule): void {
        AlertRuleService::FlushCache();
    }

    public function deleted(AlertRule $alertRule): void {
        AlertRuleService::FlushCache();
    }

    public function restored(AlertRule $alertRule): void {
        AlertRuleService::FlushCache();
    }

    public function forceDeleted(AlertRule $alertRule): void {
        AlertRuleService::FlushCache();
    }

}
