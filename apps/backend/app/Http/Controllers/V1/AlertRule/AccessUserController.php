<?php

namespace App\Http\Controllers\V1\AlertRule;


use App\Http\Controllers\Controller;
use App\Models\AlertRule;
use App\Models\User;
use App\Services\AlertRuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccessUserController extends Controller
{


    public function CreateData($id)
    {

        $alert = AlertRule::where('_id', $id)->firstOrFail();
        $currentUser = \Auth::user();

        $selectableUsers = [];
        if ($currentUser->isAdmin() || $alert->userId == $currentUser->_id) {
            $selectableUsers = User::whereNotIn('_id',[$currentUser->id,])->get();
        } else {
            abort(403);
        }

        $alertUsers = [];
        if (!empty($alert->user_ids))
            $alertUsers = User::whereIn("_id", $alert->user_ids)->get();

        return response()->json(compact('alertUsers', 'selectableUsers'));
    }


    public function Store(Request $request, $id)
    {

        $currentUser = Auth::user();
        $isAdmin = $currentUser->isAdmin();
        $alert = AlertRule::where('_id', $id)->firstOrFail();

        if(!$isAdmin && $alert->userId != $currentUser->id){
            abort(403);
        }
        if ($request->has("user_ids") && !empty($request->post("user_ids"))) {

            foreach ($request->user_ids as $userId) {
                $alert->push("user_ids", $userId, true);
            }
            $alert->save();
        }

        return response()->json(['status' => true]);
    }

    public function Delete($alertId, $userId)
    {

        $alert = AlertRule::where('_id', $alertId)->firstOrFail();
        if(!Auth::user()->isAdmin() && $alert->userId != Auth::user()->id){
            abort(403);
        }

        $alert->pull("user_ids", $userId);
        $alert->save();

        return response()->json(['status' => true]);
    }


}
