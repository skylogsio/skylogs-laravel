<?php

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Models\Endpoint;
use App\Models\EndpointOTP;
use App\Services\EndpointService;
use Illuminate\Http\Request;


class EndpointController extends Controller
{


    public function Index(Request $request)
    {
        $perPage = $request->per_page ?? 25;

        $data = Endpoint::query();
        $isAdmin = auth()->user()->isAdmin();
        if ($isAdmin) {
            $data = $data->where("userId", auth()->id());
        }

        $data = $data->paginate($perPage);

        return response()->json($data);
    }

    public function Show(Request $request, $id)
    {
        $model = Endpoint::where('_id', $id);
        $isAdmin = auth()->user()->isAdmin();
        if ($isAdmin) {
            $model = $model->where("userId", auth()->id());
        }
        $model = $model->firstOrFail();
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

            if ($request->type == "telegram") {
                $model = Endpoint::create([
                    'user_id' => \Auth::id(),
                    'userId' => \Auth::id(),
                    'name' => $request->name,
                    'type' => $request->type,
                    'chatId' => $value,
                    'threadId' => $request->threadId,
                ]);
            } else {
                $model = Endpoint::create([
                    'user_id' => \Auth::id(),
                    'userId' => \Auth::id(),
                    'name' => $request->name,
                    'type' => $request->type,
                    'value' => $value,
                ]);
                if ($model->isVerifiedRequired()){
                    $model->generateOtpCode();
                    $model->save();
                }
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
        ],[

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

            if ($request->type == "telegram") {
                $model->update([
                    'name' => $request->name,
                    'type' => $request->type,
                    'chatId' => $value,
                    'threadId' => $request->threadId,
                ]);
            } else {
                $model->update([
                    'name' => $request->name,
                    'type' => $request->type,
                    'value' => $value,
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


}
