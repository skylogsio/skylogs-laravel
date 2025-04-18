<?php

namespace App\Services;

use App\Enums\DataSourceType;
use App\Models\AlertRulePrometheus;

use App\Models\DataSource\DataSource;
use App\Models\PrometheusInstance;

use App\Models\Service;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class PrometheusInstanceService
{


    public static function getLabels(): array
    {
//        if (empty($instance)) {
        return self::getAllLabels();
//        } else {
//            return self::getRulesInstance($instance);
//        }

    }

    public static function getLabelValues($label): array
    {
//        if (empty($instance)) {
        return self::getAllLabelValues($label);
//        } else {
//            return self::getRulesInstance($instance);
//        }

    }

    public static function GetRulesByName()
    {
        $prometheusAll = Service::where('type', 'prometheus');

        if (!empty($names))
            $prometheusAll = $prometheusAll->whereIn('name', $names);

        $prometheusAll = $prometheusAll->get();
        $alerts = [];
        $responses = [];
        if ($prometheusAll->isNotEmpty()) {

            $responses = Http::pool(function (Pool $pool) use ($prometheusAll) {
                $result = [];
                foreach ($prometheusAll as $pro) {

                    $request = $pool->as($pro->name)->acceptJson();
                    if (!empty($pro->username) && !empty($pro->password)) {
                        $request = $request->withBasicAuth($pro->username, $pro->password);
                    }
                    $result[] = $request->get($pro->getPrometheusRulesUrl(),);
                }

                return $result;
            });


            foreach ($responses as $name => $response) {

                try {
//                    ds($response::class);
//                    ds($response->ok());
                    if (!($response instanceof Response && $response->ok())) continue;

                    $response = $response->json();

                    $ruleArr = $response['data']['groups'];
                    foreach ($ruleArr as $group) {

                        foreach ($group['rules'] as $rule) {
                            $severity = $rule['labels']['severity'] ?? "";

                            if(empty($severity))
                                continue;

                            if(empty($alerts[$rule['name']])) {
                                $alerts[$rule['name']] = [
                                    'name' => $rule['name'],
                                    "instance" => [$name],
                                    "severity" => $severity,
                                ];
                            }else{
                                $array = $alerts[$rule['name']]['instance'];
                                array_push($array,$name);
                                $alerts[$rule['name']]['instance'] = array_unique($array);
                            }

                        }

                    }


                } catch (\Exception $e) {

                }


            }


        }


        return $alerts;
    }

    public static function getRules($dataSourceId = null): array
    {
        if (empty($dataSourceId)) {
            return self::getAllRules();
        } else {
            return self::getRulesInstance($dataSourceId);
        }

    }

    private static function getAllLabelValues($label)
    {
        $prometheusAll = DataSourceService::Get(DataSourceType::PROMETHEUS);
        $resultLabels = [];
        $responses = [];
        if ($prometheusAll->isNotEmpty()) {

            $responses = Http::pool(function (Pool $pool) use ($prometheusAll, $label) {
                $result = [];
                foreach ($prometheusAll as $pro) {

                    $request = $pool->as($pro->name)->acceptJson();
                    if (!empty($pro->username) && !empty($pro->password)) {
                        $request = $request->withBasicAuth($pro->username, $pro->password);
                    }
                    $result[] = $request->get($pro->prometheusGetLabelsValueUrl($label),);
                }

                return $result;
            });
//            dd($responses);

            foreach ($responses as $name => $response) {

                try {
//                    ds($response::class);
//                    ds($response->ok());
                    if (!($response instanceof Response && $response->ok())) continue;

                    $response = $response->json();
                    $labels = $response['data'];
                    foreach ($labels as $label) {
                        $resultLabels[$label] = $label;
                    }

                } catch (\Exception $e) {
                }

            }

        }

        return $resultLabels;
    }

    private static function getAllLabels()
    {
        $prometheusAll = DataSourceService::Get(DataSourceType::PROMETHEUS);
        $resultLabels = [];
        $responses = [];
        if ($prometheusAll->isNotEmpty()) {

            $responses = Http::pool(function (Pool $pool) use ($prometheusAll) {
                $result = [];
                foreach ($prometheusAll as $pro) {

                    $request = $pool->as($pro->name)->acceptJson();
                    if (!empty($pro->username) && !empty($pro->password)) {
                        $request = $request->withBasicAuth($pro->username, $pro->password);
                    }
                    $result[] = $request->get($pro->promethusGetLabelsUrl(),);
                }

                return $result;
            });
//            dd($responses);

            foreach ($responses as $name => $response) {

                try {
//                    ds($response::class);
//                    ds($response->ok());
                    if (!($response instanceof Response && $response->ok())) continue;

                    $response = $response->json();
                    $labels = $response['data'];
                    foreach ($labels as $label) {
                        $resultLabels[$label] = $label;
                    }


                } catch (\Exception $e) {

                }


            }


        }


        return $resultLabels;
    }

    private static function getAllRules($names = [])
    {
        $prometheusAll = PrometheusInstance::query();

        if (!empty($names))
            $prometheusAll = $prometheusAll->whereIn('name', $names);

        $prometheusAll = $prometheusAll->get();
        $alerts = [];
        $responses = [];
        if ($prometheusAll->isNotEmpty()) {

            $responses = Http::pool(function (Pool $pool) use ($prometheusAll) {
                $result = [];
                foreach ($prometheusAll as $pro) {

                    $request = $pool->as($pro->name)->acceptJson();
                    if (!empty($pro->username) && !empty($pro->password)) {
                        $request = $request->withBasicAuth($pro->username, $pro->password);
                    }
                    $result[] = $request->get($pro->getRulesUrl(),);
                }

                return $result;
            });


            foreach ($responses as $name => $response) {

                try {
//                    ds($response::class);
//                    ds($response->ok());
                    if (!($response instanceof Response && $response->ok())) continue;

                    $response = $response->json();

                    $ruleArr = $response['data']['groups'];
                    foreach ($ruleArr as $group) {
                        foreach ($group['rules'] as $rule) {
                            $model = new AlertRulePrometheus();
                            $model->instance = $name;
                            $model->name = $rule['name'];
                            $model->queryString = $rule['query'];
                            $model->duration = $rule['duration'] ?? "";
                            $model->severity = $rule['labels']['severity'] ?? "";
                            if(empty($model->severity))
                                continue;
                            $alerts[$model->name] = $model;
                        }

                    }


                } catch (\Exception $e) {

                }


            }


        }


        return $alerts;
    }

    private static function getRulesInstance($id)
    {
        $pro = DataSource::where("id", $id)->first();
        $alerts = [];

        try {


            $request = \Http::acceptJson();
            if (!empty($pro->username) && !empty($pro->password)) {
                $request = $request->withBasicAuth($pro->username, $pro->password);
            }
            $response = $request->get($pro->prometheusGetRulesUrl())->json();


            $ruleArr = $response['data']['groups'];
            foreach ($ruleArr as $group) {
//                ds($group);

                foreach ($group['rules'] as $rule) {
                    $model = new AlertRulePrometheus();
                    $model->instance = $pro->name;
                    $model->name = $rule['name'];
                    $model->queryString = $rule['query'];
                    $model->duration = $rule['duration'] ?? "";
                    $model->severity = empty($rule['labels']) ? "" : (empty($rule['labels']['severity']) ? "" : $rule['labels']['severity']);
                    $alerts[] = $model;
                }
            }


        } catch (\Exception $e) {

        }


        return $alerts;
    }

    public static function getTriggered(): array
    {

        $prometheusAll = DataSourceService::Get(DataSourceType::PROMETHEUS);
        $alerts = [];

        $responses = [];
        if ($prometheusAll->isNotEmpty()) {

            $responses = Http::pool(function (Pool $pool) use ($prometheusAll) {
                $result = [];
                /** @var $pro DataSource */
                foreach ($prometheusAll as $pro) {

                    $request = $pool->as($pro->id)->acceptJson();
                    if (!empty($pro->username) && !empty($pro->password)) {
                        $request = $request->withBasicAuth($pro->username, $pro->password);
                    }
                    $result[] = $request->get($pro->prometheusGetAlertsUrl(),);
                }

                return $result;
            });


            foreach ($responses as $id => $response) {
                if (!($response instanceof Response && $response->ok())) continue;
                $response = $response->json();

                $arr = $response['data']['alerts'];

                foreach ($arr as &$alert) {
                    $alert['data_source_id'] = $id;

                    if ($alert['state'] == PrometheusInstance::STATE_FIRING) {
                        $alerts[] = $alert;
                    }

                }
            }


        }

        return $alerts;

    }

    public static function getMetricLabels($id, $metric)
    {
        $prometheus = DataSource::where("id", $id)->first();
        $prometheusAll = DataSourceService::Get(DataSourceType::PROMETHEUS);

        $labels = [];


        try {
            $request = \Http::acceptJson();
            if (!empty($prometheus->username) && !empty($prometheus->password)) {
                $request = $request->withBasicAuth($prometheus->username, $prometheus->password);
            }
            $response = $request->get($prometheus->prometheusGetQueryUrl($metric))->json();

            $webMetrics = $response['data']['result'];
            foreach ($webMetrics as $webMetric) {
                foreach ($webMetric['metric'] as $label => $value) {
                    if (empty($labels[$label])) {
                        $labels[$label] = [];
                    }
                    if (!in_array($value, $labels[$label])) {
                        $labels[$label][] = $value;
                    }
                }
            }

        } catch (\Exception $exception) {
            $labels = [];
        }
        return $labels;
    }

    public static function getHealthCheck(PrometheusInstance $prometheusInstance)
    {
        try {
            $request = \Http::acceptJson();
            if (!empty($prometheusInstance->username) && !empty($prometheusInstance->password)) {
                $request = $request->withBasicAuth($prometheusInstance->username, $prometheusInstance->password);
            }
            $response = $request->timeout(3)->get($prometheusInstance->getAlertsUrl());

            return $response->status() == 200;
        } catch (\Exception $e) {
            return false;
        }
    }
}
