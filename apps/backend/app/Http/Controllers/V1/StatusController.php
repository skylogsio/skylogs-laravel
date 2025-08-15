<?php

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Models\AlertRule;
use App\Models\Endpoint;
use App\Models\EndpointOTP;
use App\Models\Status;
use App\Models\User;
use App\Services\EndpointService;
use Illuminate\Http\Request;


class StatusController extends Controller
{

    public function Index(Request $request)
    {

        $perPage = $request->perPage ?? 25;

        $data = Endpoint::query();
        $isAdmin = auth()->user()->isAdmin();
        if (!$isAdmin) {
            $data = $data->where("userId", auth()->id());
        }
        if ($request->filled('name')) {
            $data->where('name', 'like', '%' . $request->name . '%');
        }

        $data = $data->paginate($perPage);

        return response()->json($data);

    }

    public function Status(Request $request)
    {
        $statuses = Status::get();
        $result = collect();
        foreach ($statuses as $status) {
            $query = AlertRule::query();

            if (!empty($status->filters)) {
                $filters = $status->filters;

                foreach ($filters as $key => $value) {
                    $query = $query->where("labels.$key", $value);
                }
            } else {
                $result[$status->_id] = ["name" => $status->name, "state" => AlertRule::RESOlVED,];
                continue;
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


            $result[$status->_id] = [
                "id" => $status->id,
                "name" => $status->name,
                "state" => $statusState,
                "countCritical" => $numberCritical,
                "countWarning" => $numberWarning
            ];

        }

        return response()->json($result);

    }




    public function Store(Request $request)
    {


        $va = \Validator::make(
            $request->all(),
            [
                'name' => "required|unique:status",
                'tags' => "required|array",
            ],
        );


        if ($va->passes() && count($request->tags) != 0) {
            $status = Status::create([
                'name' => $request->name,
                "tags" => $request->tags,
            ]);
            $extraFields = [];

            if ($request->has("extraField") && !empty($request->extraField))
                foreach ($request->extraField as $value) {
                    if (!empty($value)) {
                        if (!empty($value["key"]) && !empty($value["value"]))
                            $extraFields[$value["key"]] = $value['value'];
                    }
                }

            $status->filters = $extraFields;
            $status->save();
            return ['status' => true];
        } else {
            return ['status' => false];
        }
    }

    public function StoreUpdate(Request $request, $id)
    {


        $va = \Validator::make(
            $request->all(),
            [
                'name' => "required|unique:status",
                'tags' => "required|array",
            ],
        );

        if ($va->passes() && count($request->tags) != 0) {

            $status = Status::where("id", $id)->firstOrFail();

            $extraFields = [];

            if ($request->has("extraField") && !empty($request->extraField))
                foreach ($request->extraField as $value) {
                    if (!empty($value)) {
                        if (!empty($value["key"]) && !empty($value["value"]))
                            $extraFields[$value["key"]] = $value['value'];
                    }
                }


            $status->filters = $extraFields;
            $status->name = $request->name;
            $status->tags = $request->tags;
            $status->save();
            return ['status' => true];
        } else {
            return ['status' => false];
        }
    }


    public function Delete(Request $request,$id)
    {

        $model = Status::where('_id', $id)->firstOrFail();

        $model->delete();
        return ['status' => true];
    }



}
