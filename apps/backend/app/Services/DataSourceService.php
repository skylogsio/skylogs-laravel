<?php

namespace App\Services;

use App\Enums\DataSourceType;
use App\Models\DataSource\DataSource;
use Illuminate\Database\Eloquent\Collection;


class DataSourceService
{


    public function get(DataSourceType $dataSourceType = null): Collection
    {
        $tagsArray = ['dataSource'];
        $keyName = 'dataSource';
        if ($dataSourceType) {
            $tagsArray[] = $dataSourceType->value;
            $keyName .= ':' . $dataSourceType->value;
        }

        $dataSources = cache()
            ->tags($tagsArray)
            ->rememberForever($keyName, function () use ($dataSourceType) {
                $dataSource = DataSource::query();
                if ($dataSourceType) {
                    $dataSource = $dataSource->where('type', $dataSourceType);
                }
                return $dataSource->get()->keyBy('id');
            });

        return $dataSources;
    }

    public static function flushCache()
    {
        cache()->tags(['dataSource'])->flush();
    }

}
