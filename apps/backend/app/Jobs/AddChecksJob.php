<?php

namespace App\Jobs;

use App\Enums\AlertRuleType;
use App\Services\AlertRuleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class AddChecksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $alertRules = app(AlertRuleService::class)->getAlerts(AlertRuleType::ELASTIC);

        foreach ($alertRules as $alert) {
            CheckElasticJob::dispatch($alert);
        }

    }

}
