<?php

namespace App\Services;

use App\Enums\AlertRuleType;
use App\Models\AlertRule;
use App\Models\Profile\ProfileAsset;

class ProfileService
{

    public function __construct(protected DataSourceService $dataSourceService)
    {
    }

    public function createAlertRules(ProfileAsset $profileAsset)
    {

        $config = json_decode($profileAsset->config,true);
        $userId = $profileAsset->ownerId;
        $createdAlerts = collect();
        foreach ($config as $env => $envConfig) {
            foreach ($envConfig as $serviceType => $serviceConfig) {
                foreach ($serviceConfig as $dataSourceToken => $dataSourceConfig) {

                    switch (AlertRuleType::from($serviceType)) {
                        case AlertRuleType::PMM:
                        case AlertRuleType::PROMETHEUS:
                        case AlertRuleType::GRAFANA:
                            $alerts = $this->generateGrafanaPrometheusPmmAlert($serviceType,$userId,$profileAsset->name, $env, $dataSourceToken,$dataSourceConfig);
                            $createdAlerts = $createdAlerts->concat($alerts);
                            break;
                    }


                }
            }

        }

        return $createdAlerts;
    }

    private function generateGrafanaPrometheusPmmAlert($type,$userId, $service, $env, $dataSourceToken, $config)
    {
        $dataSource = $this->dataSourceService->byToken($dataSourceToken);
        $tags = collect($config["tags"])->push($service,$env);
        $resultAlerts = [];
        $commonFields = [
            'type' => $type,
            "dataSourceIds" => [$dataSource->id],
            "queryType" => AlertRule::DYNAMIC_QUERY_TYPE,
            "queryText" => "",

            'tags' => $tags->toArray(),
            "userId" => $userId,
            "silentUserIds" => [],
            "endpointIds" => [],
            "userIds" => [],
        ];
        $commonLabels = collect($config["labels"]);
        if (!empty($config['alertnames'])) {

            foreach ($config['alertnames'] as $alertConfig) {
                if(is_array($alertConfig)){
                    $alertRuleName =$alertConfig['name']."-". $service . "-" . $env;
                    $dataSourceAlertname = $alertConfig['name'];
                    $extraField = $commonLabels->merge($alertConfig['labels']);
                }else{
                    $alertRuleName =$alertConfig."-". $service . "-" . $env ;
                    $dataSourceAlertname = $alertConfig;
                    $extraField = $commonLabels->toArray();

                }

                $resultAlerts[] =  AlertRule::firstOrCreate([
                    "name" => $alertRuleName,
                ],[
                    ...$commonFields,
                    "name" => $alertRuleName,
                    "dataSourceAlertName" => $dataSourceAlertname,
                    "extraField" => $extraField,
                ]);
            }

        } else {
            $alertRuleName = $dataSource->name . "-" . $service . "-" . $env ;


            $resultAlerts[] = AlertRule::firstOrCreate([
                "name" => $alertRuleName,
            ],[
                ...$commonFields,
                "name" => $alertRuleName,
                "dataSourceAlertName" => "",
                "extraField" => $commonLabels->toArray(),
            ]);
        }

        return $resultAlerts;

    }


}
