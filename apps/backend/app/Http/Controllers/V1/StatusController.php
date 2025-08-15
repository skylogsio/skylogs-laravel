<?php

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Models\AlertRule;
use App\Models\Endpoint;
use App\Models\EndpointOTP;
use App\Models\Status;
use App\Models\User;
use App\Services\EndpointService;
use App\Services\StatusService;
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
        $result = app(StatusService::class)->getAllState();

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
            $tags = collect($request->tags)->map(fn($item) => trim($item))->unique()->toArray();

            $status = Status::create([
                'name' => $request->name,
                "tags" => $tags,
            ]);

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
            $tags = collect($request->tags)->map(fn($item) => trim($item))->unique()->toArray();

            $status = Status::where("id", $id)->firstOrFail();

            $status->name = $request->name;
            $status->tags = $tags;
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
