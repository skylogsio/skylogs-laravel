<?php

namespace App\Services;

use App\Enums\ClusterType;
use App\Models\Endpoint;
use App\Models\SkylogsInstance;
use App\Models\User;
use Illuminate\Container\Attributes\Config;

class ClusterService
{
    private ClusterType $clusterType;

    public function __construct(
        #[Config("app.clusterType")]
        $clusterType,
    )
    {
        $this->clusterType = ClusterType::from($clusterType);
    }

    public function type():ClusterType
    {
        return $this->clusterType;
    }

    public function clusterByToken($token):?SkylogsInstance
    {
        return SkylogsInstance::where('token', $token)->first();
    }

    public function syncData()
    {
        if($this->clusterType == ClusterType::AGENT){
            $sourceUrl = config("app.sourceUrl");
            $sourceToken = config("app.sourceToken");

            $response  = \Http::withToken($sourceToken)->get($sourceUrl."/api/cluster/sync-data");
            $users = $response->json();

            foreach($users as $user){

                $model = User::where("username", $user["username"])->firstOrNew();
                $model->name = $user["name"];
                $model->username = $user["username"];
                $model->password = $user["password"];
                $model->save();

                $roles = collect($user["roles"])->pluck("name");
                foreach($roles as $role){
                    if(!$model->hasRole($role)){
                        foreach ($model->roles as $userRole) {
                            $model->removeRole($userRole);
                        }
                        $model->syncRoles($role);
                    }
                }

                $endpoints = collect($user["endpoints"]);
                foreach($endpoints as $endpoint){
                    $endpointModel = Endpoint::where("id", $endpoint["id"])->firstOrNew();
                    $endpointModel->_id = $endpoint['id'];
                    $endpointModel->userId = $model->id;
                    $endpointModel->name = $endpoint["name"];
                    $endpointModel->type = $endpoint["type"];
                    $endpointModel->value = $endpoint["value"];
                    $endpointModel->chatId = $endpoint["chatId"];
                    $endpointModel->threadId = $endpoint["threadId"];
                    $endpointModel->botToken = $endpoint["botToken"];
                    $endpointModel->isPublic = $endpoint["isPublic"];
                    $endpointModel->save();
                }
            }



        }
    }



}
