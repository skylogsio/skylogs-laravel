<?php

namespace App\Services;

use App\Models\AlertRule;
use App\Models\ApiAlertHistory;
use App\Helpers\Constants;
use App\Models\Status;
use App\Models\StatusHistory;
use Carbon\Carbon;

class StatusService
{

    public function refresh()
    {
        $statuses = Status::get();
        $result = collect();
        foreach ($statuses as $status) {
            $query = AlertRule::query();

            $tags = $status->tags;

            if ($tags) {
                $query = $query->where("tags", "all", $tags);
            }

            $alerts = $query->get();

            $numberCritical = 0;
            $numberWarning = 0;
            $isCritical = false;
            $isWarning = false;
            foreach ($alerts as $alert) {
                list($alertState, $alertCount) = $alert->getStatus();
                if ($alertState == AlertRule::RESOlVED || $alertState == AlertRule::UNKNOWN) {
                    continue;
                } elseif ($alertState == AlertRule::WARNING) {
                    $isWarning = true;
                    $numberWarning++;
                } elseif ($alertState == AlertRule::CRITICAL) {
                    $isCritical = true;
                    $numberCritical++;
                } elseif ($alertState > 0) {
                    $isCritical = true;
                    $numberCritical++;
                }
            }

            if ($isCritical) {
                $statusState = AlertRule::CRITICAL;
            } elseif ($isWarning) {
                $statusState = AlertRule::WARNING;
            } else {
                $statusState = AlertRule::RESOlVED;
            }


            $status->state = $statusState;
            $status->criticalCount = $numberCritical;
            $status->warningCount = $numberWarning;
            if ($status->isDirty()) {

                $status->save();
                StatusHistory::create([
                    "statusId" => $status->id,
                    "alertRuleIds" => $alerts->pluck("id")->toArray(),
                    "criticalCount" => $status->criticalCount,
                    "warningCount" => $status->warningCount
                ]);
            }


        }
        return $result;
    }

}
