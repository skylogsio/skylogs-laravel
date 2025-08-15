<?php

namespace App\Services;

use App\Models\AlertRule;
use App\Models\ApiAlertHistory;
use App\Helpers\Constants;
use App\Models\Status;
use Carbon\Carbon;

class StatusService
{

    public static function getDateRanges(Carbon $fromDate, Carbon $toDate, $status): array
    {
        $query = AlertRule::query();

        if (!empty($status->filters)) {
            $filters = $status->filters;

            foreach ($filters as $key => $value) {
                $query = $query->where("labels.$key", $value);
            }
        } else {
            return [];
        }
        $alerts = $query->get();

        foreach ($alerts as $alert) {
            switch ($alert->type) {
                case Constants::API:
                    ApiAlertHistory::whereBetween("createdAt", [$fromDate->toDateTime(), $toDate->toDateTime()])
                        ->get();
                    break;
            }
        }


    }

    public function getAllState()
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


            $result[] = [
                "id" => $status->id,
                "name" => $status->name,
                "state" => $statusState,
                "countCritical" => $numberCritical,
                "countWarning" => $numberWarning
            ];

        }
        return $result;
    }

}
