<?php

namespace App\Services;

use App\Models\AlertRule;
use App\Models\ApiAlertHistory;
use App\Helpers\Constants;
use Carbon\Carbon;

class TagService
{

    public static function All(): array
    {
        return cache()->tags(['alertRule','tags'])->rememberForever('alertRule:tags', function () {

            $array = AlertRule::select('tags')->distinct()->get()->toArray();

            return collect($array)->flatten()->toArray();
        });

    }
    public static function FlushCache():void
    {
        cache()->tags(['alertRule','tags'])->flush();
    }

}
