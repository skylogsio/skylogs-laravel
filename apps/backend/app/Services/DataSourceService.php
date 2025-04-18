<?php

namespace App\Services;

use App\Enums\DataSourceType;
use App\Jobs\SendNotifyJob;
use App\Models\AlertInstance;
use App\Models\AlertRule;
use App\Models\DataSource\DataSource;
use App\Models\SentryWebhookAlert;
use Illuminate\Database\Eloquent\Collection;


class DataSourceService
{


    public static function Get(DataSourceType $dataSourceType = null) : Collection
    {
        $tagsArray = ['data_source'];
        $keyName = 'data_source';
        if ($dataSourceType) {
            $tagsArray[] = $dataSourceType->value;
            $keyName .= ':' . $dataSourceType->value;
        }

        return cache()->tags($tagsArray)->rememberForever($keyName, function () use ($dataSourceType) {
            $dataSource = DataSource::query();
            if ($dataSourceType) {
                $dataSource = $dataSource->where('type', $dataSourceType);
            }
            return $dataSource->get();
        });
    }
    public static function FlushCache(){
        cache()->tags(['data_source'])->flush();
    }

}
