<?php

namespace App\Http\Controllers\V1\AlertRule;


use App\Http\Controllers\Controller;
use App\Models\AlertRule;
use App\Services\AlertRuleService;
use App\Services\TagService;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagsController extends Controller
{


    public function All()
    {

        return response()->json(TagService::All());
    }

    public function Create(Request $request, $id)
    {
        $alert = AlertRule::where('_id', $id)->first();
        $currentUser = Auth::user();
        if (!AlertRuleService::HasAdminAccessAlert($currentUser, $alert)) {
            abort(403);
        }
        $tags = $alert->tags ?? [];
        return  response()->json($tags);
    }

    public function Store(Request $request, $id)
    {

        if(!empty($request->tags) && !is_array($request->tags)) {
            abort(422);
        }
        $alert = AlertRule::where('_id', $id)->first();
        $currentUser = Auth::user();
        $access = AlertRuleService::HasAdminAccessAlert($currentUser, $alert);

        $tags = collect($request->tags ?? [])->map(fn($item) => trim($item))->unique()->toArray();
        if (!$access) {
            abort(403);
        }

        $alert->tags = $tags;
        $alert->save();
        TagService::FlushCache();

        return response()->json(["status" => true,]);

    }



}
