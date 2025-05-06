<?php

namespace App\Http\Controllers\V1\AlertRule;


use App\Enums\DataSourceType;
use App\Http\Controllers\Controller;
use App\Models\DataSource\DataSource;
use App\Models\Endpoint;
use App\Models\User;
use App\Services\PrometheusInstanceService;
use Excel;
use Illuminate\Http\Request;

class CreateDataController extends Controller
{

    public function CreateData(Request $request)
    {

        $prometheusDataSources = DataSource::where("type", DataSourceType::PROMETHEUS)->get()->pluck("name");
        $grafanaDataSources = DataSource::where("type", DataSourceType::GRAFANA)->get()->pluck("name");
        $splunkDataSources = DataSource::where("type", DataSourceType::SPLUNK)->get()->pluck("name");

        $adminUserId = User::where('username', 'admin')->first()->_id;

        $endpoints = Endpoint::whereIn("userId", [$adminUserId, \Auth::user()->_id])->get();
        $users = User::whereNotIn("_id", [$adminUserId, \Auth::id()])->get();

        return response()->json(
            compact(
                "prometheusDataSources",
                "grafanaDataSources",
                "endpoints",
                'splunkDataSources',
                "users"
            )
        );

    }

    public function DataSources(Request $request,$type)
    {
        $type = DataSourceType::tryFrom($type);
        $dataSources = DataSource::where("type", $type)->get();

        $result = [];
        foreach ($dataSources as $dataSource) {
            $result[] = [
                "name" => $dataSource->name,
                'id' => $dataSource->id,
            ];
        }

        return response()->json($result);

    }

    public function Rules(Request $request)
    {
        return response()->json(PrometheusInstanceService::getRules($request->data_source_id));
    }
    public function Labels(Request $request)
    {
        return response()->json(PrometheusInstanceService::getLabels());
    }
    public function LabelValues(Request $request,$label)
    {
        return response()->json(PrometheusInstanceService::getLabelValues($label));
    }


}
