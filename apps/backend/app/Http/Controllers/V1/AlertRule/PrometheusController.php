<?php

namespace App\Http\Controllers\V1\AlertRule;


use App\Http\Controllers\Controller;
use App\Services\PrometheusInstanceService;
use Excel;
use Illuminate\Http\Request;

class PrometheusController extends Controller
{


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
