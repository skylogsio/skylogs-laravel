<?php

namespace App\Jobs;

use App\Interfaces\Messageable;
use App\Models\AlertRule;
use App\Models\ElasticCheck;
use App\Models\ElasticHistory;
use App\Models\HealthCheck;
use App\Models\Log;
use App\Services\ElasticService;
use App\Services\PrometheusInstanceService;
use App\Services\SendNotifyService;
use App\Helpers\Constants;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use function Symfony\Component\Translation\t;

class CheckElasticJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $alert;

    public function __construct($alert)
    {
        $this->onQueue('httpRequests');
        $this->alert = $alert;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $check = ElasticCheck::firstOrCreate(
            [
                "alertRuleId" => $this->alert->_id
            ],
            [
                "dataview_name" => $this->alert->dataview_name,
                "dataview_title" => $this->alert->dataview_title,
                "query_string" => $this->alert->query_string,
                "minutes" => $this->alert->minutes,
                "count_document" => $this->alert->count_document,
                "state" => ElasticCheck::RESOLVED,
            ]
        );

        $documents = ElasticService::getDocuments($check);
        $check->refresh();
        if(empty($this->alert->condition) || $this->alert->condition == ElasticCheck::CONDITION_TYPE_GREATER_OR_EQUAL ){
            $isFired = count($documents) >= $check->count_document;
        }else{
            $isFired = count($documents) <= $check->count_document;
        }

        if ($isFired) {
            if ($check->state != ElasticCheck::FIRE) {
                $check->state = ElasticCheck::FIRE;

                $alertRule = $check->alertRule;
                $alertRule->notify_at = time();
                $alertRule->state = AlertRule::CRITICAL;
                $alertRule->save();

                $check->save();

                ElasticHistory::create([
                    "alertRuleId" => $this->alert->_id,
                    "alertRuleName" => $this->alert->alertname,
                    "dataview_name" => $this->alert->dataview_name,
                    "dataview_title" => $this->alert->dataview_title,
                    "query_string" => $this->alert->query_string,
                    "minutes" => $this->alert->minutes,
                    "count_document" => $this->alert->count_document,
                    "current_count_document" => count($documents),
                    "state" => ElasticCheck::FIRE,
                ]);

                SendNotifyService::CreateNotify(SendNotifyJob::HEALTH_CHECK, $check,$this->alert->_id);
            }

        } else {
            if ($check->state != ElasticCheck::RESOLVED) {
                $check->state = ElasticCheck::RESOLVED;
                $alertRule = $check->alertRule;
                $alertRule->notify_at = time();
                $alertRule->state = AlertRule::RESOlVED;
                $alertRule->save();

                $check->save();

                ElasticHistory::create([
                    "alertRuleId" => $this->alert->_id,
                    "alertRuleName" => $this->alert->alertname,
                    "dataview_name" => $this->alert->dataview_name,
                    "dataview_title" => $this->alert->dataview_title,
                    "query_string" => $this->alert->query_string,
                    "minutes" => $this->alert->minutes,
                    "count_document" => $this->alert->count_document,
                    "current_count_document" => count($documents),
                    "state" => ElasticCheck::RESOLVED,
                ]);

                SendNotifyService::CreateNotify(SendNotifyJob::HEALTH_CHECK, $check,$this->alert->_id);

            }
        }


    }

    public function uniqueId()
    {
        return $this->alert->_id;
    }

}
