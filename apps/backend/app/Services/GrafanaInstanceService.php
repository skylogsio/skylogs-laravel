<?php

namespace App\Services;

use App\Enums\DataSourceType;
use App\Models\AlertRuleGrafana;
use App\Models\GrafanaInstance;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class GrafanaInstanceService
{

    protected DataSourceService $dataSourceService;

    public function __construct(DataSourceService $dataSourceService)
    {
        $this->dataSourceService = $dataSourceService;
    }

    protected function resolveDataSources(?int $dataSourceId): Collection
    {
        if ($dataSourceId) {
            $source = $this->dataSourceService->get(DataSourceType::GRAFANA)->get($dataSourceId);
            return $source ? collect([$dataSourceId => $source]) : collect();
        }

        return $this->dataSourceService->get(DataSourceType::GRAFANA);
    }

    public function alertRulesName(?string $dataSourceId = null): Collection
    {
        $dataSources = $this->resolveDataSources($dataSourceId);

        if ($dataSources->isEmpty()) {
            return collect();
        }

        $this->fetchOrganizations($dataSources);
        return $this->fetchRules($dataSources)->pluck('title')->unique();

    }

    private function fetchRules($dataSources)
    {


        $requestsData = [];
        $rulesResponses = Http::pool(function (Pool $pool) use ($dataSources) {

            $requests = [];

            foreach ($dataSources as $dataSource) {
                $request = $pool->acceptJson();

                if (!empty($dataSource->username) && !empty($dataSource->password)) {
                    $request = $request->withBasicAuth($dataSource->username, $dataSource->password);
                }

                if (!empty($dataSource->orgs)) {
                    foreach ($dataSource->orgs as $org) {
                        $requests[] = $request
                            ->withHeader("X-Grafana-Org-Id", $org['id'])
                            ->get($dataSource->grafanaAlertRulesUrl());
                        $dataSource->org = $org;
                        $requestsData[] = $dataSource;
                    }
                } else {
                    $requests[] = $request->get($dataSource->grafanaAlertRulesUrl());
                    $requestsData[] = $dataSource;
                }

            }

            return $requests;
        });

        $alerts = collect();
        foreach ($rulesResponses as $index => $ruleResponse) {
            $dataSource = $requestsData[$index] ?? null;

            if (!($ruleResponse instanceof Response && $ruleResponse->ok())) continue;

            $response = $ruleResponse->json();
            try {
                foreach ($response as $alert) {

                    $model = new AlertRuleGrafana();
                    $model->dataSourceId = $dataSource->id;
                    $model->dataSourceName = $dataSource->name;
                    $model->organizationId = $dataSource->org['id'] ?? null;
                    $model->organizationName = $dataSource->org['name'] ?? "";
                    $model->ruleGroup = $alert["ruleGroup"];
                    $model->title = $alert["title"];
                    $model->annotations = $alert['annotations'];
                    $model->labels = $alert['labels'];
                    $alerts[] = $model;
                }
            } catch  (\Exception $e) {

            }

        }

        return $alerts;
    }


    public
    static function getTriggered(): array
    {
        $prometheusAll = GrafanaInstance::all();
        $alerts = [];

        foreach ($prometheusAll as $pro) {

            try {

                if (!empty($pro->username) && !empty($pro->password)) {
                    $response = \Http::acceptJson()->withBasicAuth($pro->username, $pro->password)->get($pro->url . "/api/v1/alerts")->json();
                } else {
                    $response = \Http::acceptJson()->get($pro->url . "/api/v1/alerts")->json();
                }

                $arr = $response['data']['alerts'];
                foreach ($arr as &$alert) {
                    $alert['instance'] = $pro->name;
                    $alerts[] = $alert;
                }
            } catch (\Exception $exception) {
                $arr = [];
            }
        }
        return $alerts;

    }

    /**
     * @param Collection $dataSources
     * @return void
     */
    public function fetchOrganizations(Collection &$dataSources): void
    {
        $orgResponses = Http::pool(function (Pool $pool) use ($dataSources) {
            $result = [];
            foreach ($dataSources as $dataSource) {
                $request = $pool->as($dataSource->id)->acceptJson();

                if (!empty($dataSource->username) && !empty($dataSource->password)) {
                    $request = $request->withBasicAuth($dataSource->username, $dataSource->password);
                }


                $result[] = $request->get($dataSource->grafanaOrganizationsUrl());
            }
            return $result;
        });

        foreach ($orgResponses as $dataSourceId => $orgResponse) {
            if (!($orgResponse instanceof Response && $orgResponse->ok())) continue;

            $response = $orgResponse->json();
            $dataSources->get($dataSourceId)->orgs = collect($response)->toArray();
        }

    }

}
