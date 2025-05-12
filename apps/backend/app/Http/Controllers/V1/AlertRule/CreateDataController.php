<?php

namespace App\Http\Controllers\V1\AlertRule;


use App\Enums\AlertRuleType;
use App\Enums\DataSourceType;
use App\Http\Controllers\Controller;
use App\Models\DataSource\DataSource;
use App\Models\Endpoint;
use App\Models\User;
use App\Services\PrometheusInstanceService;
use Illuminate\Http\Request;

class CreateDataController extends Controller
{

    public function CreateData(Request $request)
    {

        $adminUserId = User::where('username', 'admin')->first()->_id;

        $endpoints = Endpoint::where("userId", \Auth::user()->_id)
            ->orWhere('isPublic', true)
            ->get();
        $users = User::whereNotIn("_id", [$adminUserId, \Auth::id()])->get();

        return response()->json(
            compact(
                "endpoints",
                "users"
            )
        );

    }

    public function DataSources(Request $request, $type)
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

        $type = AlertRuleType::tryFrom($request->input("type"));
//        switch ($type) {
//            case AlertRuleType::PROMETHEUS:
//            default:
//                $rules = PrometheusInstanceService::getRules($request->dataSourceId);
//                break;
//        }
        $rules = [
            "rule_1",
            "rule_2",
            "rule_3",
            "rule_4",
            "rule_5",
            "rule_6",
            "rule_7",
            "rule_8",
            "rule_9",
            "rule_10",
        ];
        return response()->json($rules);
    }

    public function Labels(Request $request)
    {
//        $labels = PrometheusInstanceService::getLabels();
        $labels = [
          "label_1",
            "label_2",
            "label_3",
            "label_4",
            "label_5",
            "label_6",
        ];
        return response()->json($labels);
    }

    public function LabelValues(Request $request, $label)
    {
//        $labelValues = PrometheusInstanceService::getLabelValues($label);
        $labels = [
            "label_1"=>[
                "label_1_val_1",
                "label_1_val_2",
                "label_1_val_3",
                "label_1_val_4",
            ],
            "label_2"=>[
                "label_2_val_1",
                "label_2_val_2",
                "label_2_val_3",
                "label_2_val_4",

            ],
            "label_3"=>[
                "label_3_val_1",
                "label_3_val_2",
                "label_3_val_3",
                "label_3_val_4",
            ],
            "label_4"=>[
                "label_4_val_1",
                "label_4_val_2",
                "label_4_val_3",
                "label_4_val_4",
            ],
            "label_5"=>[
                "label_5_val_1",
                "label_5_val_2",
                "label_5_val_3",
                "label_5_val_4",
            ],
            "label_6"=>[
                "label_6_val_1",
                "label_6_val_2",
                "label_6_val_3",
                "label_6_val_4",
            ],
        ];
        return response()->json($labels[$label] ?? []);
    }


}
