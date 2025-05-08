<?php

namespace App\Services;

use App\Enums\DataSourceType;
use App\Models\DataSource\DataSource;
use Illuminate\Database\Eloquent\Collection;


class DataSourceService
{


    public static function Get(DataSourceType $dataSourceType = null): Collection
    {
        $tagsArray = ['dataSource'];
        $keyName = 'dataSource';
        if ($dataSourceType) {
            $tagsArray[] = $dataSourceType->value;
            $keyName .= ':' . $dataSourceType->value;
        }

        $prometheuses = cache()
            ->tags($tagsArray)
            ->rememberForever($keyName, function () use ($dataSourceType) {
                $dataSource = DataSource::query();
                if ($dataSourceType) {
                    $dataSource = $dataSource->where('type', $dataSourceType);
                }
                return $dataSource->get()->keyBy('id');
            });

        return $prometheuses;
    }

    public static function FlushCache()
    {
        cache()->tags(['dataSource'])->flush();
    }

}
