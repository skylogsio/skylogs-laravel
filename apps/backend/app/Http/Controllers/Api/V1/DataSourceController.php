<?php

namespace App\Http\Controllers\Api\V1;


use App\Enums\DataSourceType;
use App\Http\Controllers\Controller;
use App\Models\DataSource\DataSource;
use App\Models\Service;
use Illuminate\Http\Request;


class DataSourceController extends Controller
{


    public function Index(Request $request)
    {
        $perPage = $request->per_page ?? 25;

        $data = DataSource::query();

        $data = $data->paginate($perPage);

        return response()->json($data);
    }

    public function Show(Request $request, $id)
    {
        $model = DataSource::where('_id', $id);
        $model = $model->firstOrFail();
        return response()->json($model);
    }

    public function Delete(Request $request, $id)
    {
        $model = DataSource::where('_id', $id);
        $model = $model->firstOrFail();
        $model->delete();
        return response()->json($model);
    }

    public function Create(Request $request)
    {
        $va = \Validator::make(
            $request->all(),
            [
                'name' => "required",
                'type' => "required",
                "url" => "required",
            ],
        );
        if ($va->passes()) {
            $url = rtrim($request->url, "/");
            $modelArray = [
                "type" => $request->type,
                "name" => $request->name,
                "url" => $url,
            ];
            if ($request->has("api_token") && !empty($request->api_token)) {
                $modelArray["api_token"] = $request->api_token;
            }
            if ($request->has("username") && !empty($request->username)) {
                $modelArray["username"] = $request->username;
                $modelArray['password'] = $request->password ?? "";
            }
            $model = DataSource::create($modelArray);
            return response()->json([
                'status' => true,
                "data" => $model
            ]);
        } else {
            return response()->json([
                'status' => false,
            ]);
        }
    }

    public function Update(Request $request, $id)
    {
        $model = DataSource::where('_id', $id);
        $model = $model->firstOrFail();


        $va = \Validator::make(
            $request->all(),
            [
                'name' => "required",
                'type' => "required",
                "url" => "required",
            ],
        );

        if ($va->passes()) {
            $url = rtrim($request->url, "/");
            $modelArray = [
                "type" => $request->type,
                "name" => $request->name,
                "url" => $url,
            ];
            if ($request->has("api_token") && !empty($request->api_token)) {
                $modelArray["api_token"] = $request->api_token;
            }
            if ($request->has("username") && !empty($request->username)) {
                $modelArray["username"] = $request->username;
                $modelArray['password'] = $request->password ?? "";
            }

            $model->update($modelArray);

            return response()->json([
                'status' => true,
                'data' => $model,
            ]);
        } else {
            return response()->json([
                'status' => false,
            ]);
        }
    }

    public function GetTypes(Request $request)
    {
        return response()->json(DataSourceType::GetTypes());
    }


}
