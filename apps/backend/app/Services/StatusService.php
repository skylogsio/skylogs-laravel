<?php

namespace App\Services;

use App\Models\AlertRule;
use App\Models\ApiAlertHistory;
use App\Helpers\Constants;
use Carbon\Carbon;

class StatusService
{

    public static function getDateRanges(Carbon $fromDate,Carbon $toDate,$status): array
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

        foreach ($alerts as $alert){
            switch ($alert->type){
                case Constants::API:
                    ApiAlertHistory::whereBetween("createdAt", [$fromDate->toDateTime(),$toDate->toDateTime()])
                        ->get();
                    break;
            }
        }


    }

}
