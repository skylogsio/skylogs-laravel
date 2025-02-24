<?php

namespace App\Http\Controllers\Api\V1;


use App\Enums\Constants;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Jobs\SendNotifyJob;
use App\Models\AlertRule;
use App\Models\User;
use App\Models\MetabaseWebhookAlert;
use App\Models\SentryWebhookAlert;
use App\Models\ZabbixWebhookAlert;
use App\Services\GrafanaService;
use App\Services\SendNotifyService;
use Hash;
use Illuminate\Http\Request;
use Validator;


class UserController extends Controller
{


    public function Index(Request $request)
    {
        $perPage = $request->per_page ?? 25;

        $data = User::query();

        $data = $data->paginate($perPage);

        return UserResource::collection($data);
    }

    public function Show(Request $request, $id)
    {
        $model = User::where('_id', $id);
        $model = $model->firstOrFail();
        return response()->json($model);
    }

    public function Delete(Request $request, $id)
    {
        $model = User::where('_id', $id)->firstOrFail();

        if ($model->hasRole(Constants::ROLE_OWNER) && !auth()->user()->hasRole(Constants::ROLE_OWNER)) {
            abort(403);
        }
        $model->delete();
        return response()->json($model);
    }

    public function Create(Request $request)
    {
        \Validator::validate(
            $request->all(),
            [
                'username' => "required|unique:users,username",
                'name' => "required|string|max:255",
                'password' => "required",
                'role' => "required|in:owner,member,manager",
            ],
        );

        if (!auth()->user()->hasRole(Constants::ROLE_OWNER) &&
            $request->post('role') == Constants::ROLE_OWNER) {
            abort(403);
        }


        $model = User::create([
            'username' => $request->post('username'),
            'name' => $request->post('name'),
            'password' => \Hash::make($request->post('password')),
        ]);

        $role = match ($request->post('role')) {
            Constants::ROLE_OWNER => Constants::ROLE_OWNER,
            Constants::ROLE_MEMBER => Constants::ROLE_MEMBER,
            default => Constants::ROLE_MANAGER,
        };
        $model->assignRole($role);


        return response()->json([
            'status' => true,
            "data" => $model
        ]);

    }

    public function Update(Request $request, $id)
    {

        Validator::validate($request->all(), [
            'username' => "required|unique:users,username,{$id}",
            'name' => "required|string|max:255",
            'role' => "required|in:owner,member,manager",
        ]);

        $model = User::where('_id', $id)->firstOrFail();
        $currentUser = auth()->user();
        if (!$currentUser->hasRole(Constants::ROLE_OWNER) && $model->hasRole(Constants::ROLE_OWNER)) {
            abort(403);
        }

        $model->update([
            'username' => $request->post('username'),
            'name' => $request->post('name'),
        ]);

        $role = match ($request->post('role')) {
            Constants::ROLE_OWNER => Constants::ROLE_OWNER,
            Constants::ROLE_MEMBER => Constants::ROLE_MEMBER,
            default => Constants::ROLE_MANAGER,
        };
        $model->syncRoles($role);


        return response()->json([
            'status' => true,
            'data' => $model,
        ]);

    }

    public function ChangePassword(Request $request, $id)
    {
        Validator::validate(
            $request->all(),
            [
                'password' => "required",
                'confirmPassword' => "required|same:password",
            ],
        );

        $model = User::where('_id', $id)->firstOrFail();
        $currentUser = auth()->user();
        if (!$currentUser->hasRole(Constants::ROLE_OWNER) && $model->hasRole(Constants::ROLE_OWNER)) {
            abort(403);
        }

        $model->update([
            'password' =>   Hash::make($request->post('confirmPassword')),
        ]);

        return response()->json([
            'status' => true,
            'data' => $model,
        ]);

    }


}
