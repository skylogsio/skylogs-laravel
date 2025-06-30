<?php

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Models\Endpoint;
use App\Models\EndpointOTP;
use App\Models\User;
use App\Services\EndpointService;
use Illuminate\Http\Request;


class EndpointController extends Controller
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

    public function Show(Request $request, $id)
    {
        $model = Endpoint::where('id', $id);
        $isAdmin = auth()->user()->isAdmin();
        if (!$isAdmin) {
            $model = $model->where("userId", auth()->id());
        }
        $model = $model->firstOrFail();

        $model->botToken = $model->botToken ?? "";

        return response()->json($model);
    }

    public function Delete(Request $request, $id)
    {
        $model = Endpoint::where('_id', $id);
        $isAdmin = auth()->user()->isAdmin();

        if (!$isAdmin) {
            $model = $model->where("userId", auth()->id());
        }
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
            ],
        );
        if ($va->passes()) {
            $value = trim($request->value);
            $isPublic = $request->boolean('isPublic', false);

            if ($request->type == "telegram") {

                $model = Endpoint::create([
                    'user_id' => \Auth::id(),
                    'userId' => \Auth::id(),
                    'name' => $request->name,
                    'type' => $request->type,
                    'chatId' => $value,
                    'threadId' => $request->threadId,
                    "botToken" => $request->botToken,
                    'isPublic' => $isPublic,
                ]);
            } else {
                $model = Endpoint::create([
                    'user_id' => \Auth::id(),
                    'userId' => \Auth::id(),
                    'name' => $request->name,
                    'type' => $request->type,
                    'value' => $value,
                    'isPublic' => $isPublic,
                ]);

            }

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

    public function SendOTPCode(Request $request)
    {

        EndpointOTP::updateOrCreate([
            'type' => $request->type,
            'value' => $request->value,
        ], [

        ]);
    }

    public function ConfirmOTPCode(Request $request)
    {

    }

    public function Update(Request $request, $id)
    {
        $model = Endpoint::where('_id', $id);
        $isAdmin = auth()->user()->isAdmin();
        if (!$isAdmin) {
            $model = $model->where("userId", auth()->id());
        }
        $model = $model->firstOrFail();


        $va = \Validator::make(
            $request->all(),
            [
                'name' => "required",
                'type' => "required",
            ],
        );
        if ($va->passes()) {
            $value = trim($request->value);
            $isPublic = $request->boolean('isPublic', false);

            if ($request->type == "telegram") {
                $model->update([
                    'name' => $request->name,
                    'type' => $request->type,
                    'chatId' => $value,
                    'threadId' => $request->threadId,
                    "botToken" => $request->botToken,
                    "isPublic" => $isPublic,
                ]);
            } else {
                $model->update([
                    'name' => $request->name,
                    'type' => $request->type,
                    'value' => $value,
                    'isPublic' => $isPublic,
                ]);
            }

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


    public function ChangeOwner(Request $request, $id)
    {
        $endpoint = Endpoint::where('_id', $id);
        $isAdmin = auth()->user()->isAdmin();

        if (!$isAdmin) {
            $endpoint = $endpoint->where("userId", auth()->id());
        }

        $endpoint = $endpoint->firstOrFail();

        $toUser = User::where('id', $request->user_id)->firstOrFail();

        $endpoint->userId = $toUser->id;
        $endpoint->save();

        return response()->json([
            'status' => true,
            'message' => 'Successfully change owner'
        ]);

    }


}
