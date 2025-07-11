<?php

namespace App\Services;

use App\Enums\AlertRuleType;
use App\Jobs\SendNotifyJob;
use App\Models\AlertInstance;
use App\Models\AlertRule;
use App\Models\ApiAlertHistory;
use App\Models\ElasticCheck;
use App\Models\PrometheusCheck;
use App\Models\User;
use App\Helpers\Call;
use App\Helpers\Constants;
use App\Helpers\SMS;
use App\Helpers\Telegram;
use Cache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use MongoDB\BSON\UTCDateTime;

class AlertRuleService
{

    public function firedAlerts(string $alertRuleId)
    {
        $alertRule = AlertRule::where("id", $alertRuleId)->firstOrFail();
        switch ($alertRule->type){
            case AlertRuleType::API:
                $firedInstances = AlertInstance::where("alertRuleId", $alertRuleId)
                    ->where("state",AlertInstance::FIRE)
                    ->get();
                return $firedInstances;

            case AlertRuleType::PROMETHEUS:
                $check = PrometheusCheck::where("alertRuleId", $alertRuleId)->first();

                return $check ? ($check->alerts ?? []) : [];
        }


    }
    public static function GetAllHistory(Request $request)
    {
        if ($request->has("perPage")) {
            $perPage = (int)$request->perPage;
        } else
            $perPage = 50;

        $alerts = $request->has('alerts') ? $request->get('alerts') : [];

        if ($request->ajax()) {

            $filterCreatedAtArray = [];

            $showElastic = false;
            $showPrometheus = false;
            $showSentry = false;
            $showMetabase = false;
            $showApi = false;
            $showHealth = false;
            $showZabbix = false;


            if ($request->has(Constants::ELASTIC) && !empty($request->get(Constants::ELASTIC))) {
                $showElastic = true;
            }
            if ($request->has(Constants::PROMETHEUS) && !empty($request->get(Constants::PROMETHEUS))) {
                $showPrometheus = true;
            }
            if ($request->has(Constants::SENTRY) && !empty($request->get(Constants::SENTRY))) {
                $showSentry = true;
            }
            if ($request->has(Constants::METABASE) && !empty($request->get(Constants::METABASE))) {
                $showMetabase = true;
            }
            if ($request->has(Constants::API) && !empty($request->get(Constants::API))) {
                $showApi = true;
            }

            if ($request->has(Constants::ZABBIX) && !empty($request->get(Constants::ZABBIX))) {
                $showZabbix = true;
            }

            if ($request->has(Constants::HEALTH) && !empty($request->get(Constants::HEALTH))) {
                $showHealth = true;
            }

            if ($request->has("from") && !empty($request->from)) {
                $date = Carbon::createFromFormat("Y-m-d H:i", $request->from);
                $filterCreatedAtArray['$gte'] = new UTCDateTime($date->getTimestamp() * 1000);
            }

            if ($request->has("to") && !empty($request->to)) {
                $date = Carbon::createFromFormat("Y-m-d H:i", $request->to);
                $filterCreatedAtArray['$lte'] = new UTCDateTime($date->getTimestamp() * 1000);
            }
            $page = $request->page ?? 1;

            $query = ApiAlertHistory::raw(function ($collection) use ($filterCreatedAtArray, $alerts, $page, $perPage, $showHealth, $showZabbix, $showApi, $showPrometheus, $showSentry, $showMetabase, $showElastic) {

                $aggregationArray = [];


                /*       $aggregationArray[] = [
                           '$sort' => [
                               'createdAt' => -1,
                           ]
                       ];


                       $aggregationArray[] = [
                           '$facet' => [
                               'metadata' => [['$count' => 'totalCount']],
                               "data" => [['$skip' => ($page - 1) * $perPage], ['$limit' => $perPage]],
                           ]
                       ];*/

                if (!empty($alerts) || !empty($filterCreatedAtArray)) {
                    $matchAggregationArray = [];
                    if (!empty($alerts)) {
                        $matchAggregationArray['alertRule_id'] = ['$in' => $alerts];

                    }
                    if (!empty($filterCreatedAtArray)) {
                        $matchAggregationArray['createdAt'] = $filterCreatedAtArray;
                    }
                    $aggregationArray[] = [
                        '$match' => $matchAggregationArray
                    ];

                }

                $aggregationArray[] = [
                    '$sort' => [
                        'createdAt' => -1,
                    ]
                ];
                $aggregationArray[] = [
                    '$limit' => ($page + 1) * $perPage,
                ];

                $aggregationArray[] = [
                    '$addFields' => [
                        'alert_type' => Constants::API,
                    ]
                ];

                if ($showSentry) {
                    $pipelineArray = [];

                    if (!empty($alerts) || !empty($filterCreatedAtArray)) {
                        $matchAggregationArray = [];
                        if (!empty($alerts)) {
                            $matchAggregationArray['alertRuleId'] = ['$in' => $alerts];

                        }
                        if (!empty($filterCreatedAtArray)) {
                            $matchAggregationArray['createdAt'] = $filterCreatedAtArray;
                        }
                        $pipelineArray[] = [
                            '$match' => $matchAggregationArray
                        ];

                    }

                    $pipelineArray[] = [
                        '$sort' => [
                            'createdAt' => -1,
                        ]
                    ];
                    $pipelineArray[] = [
                        '$limit' => ($page + 1) * $perPage,
                    ];

                    $pipelineArray[] = [
                        '$addFields' => [
                            'alert_type' => Constants::SENTRY,
                        ]
                    ];

                    $aggregationArray[] = [
                        '$unionWith' => [
                            'coll' => 'sentry_webhook_alerts',
                            "pipeline" => $pipelineArray
                        ]
                    ];
                }
                if ($showMetabase) {
                    $pipelineArray = [];

                    if (!empty($alerts) || !empty($filterCreatedAtArray)) {
                        $matchAggregationArray = [];
                        if (!empty($alerts)) {
                            $matchAggregationArray['alertRuleId'] = ['$in' => $alerts];

                        }
                        if (!empty($filterCreatedAtArray)) {
                            $matchAggregationArray['createdAt'] = $filterCreatedAtArray;
                        }
                        $pipelineArray[] = [
                            '$match' => $matchAggregationArray
                        ];

                    }

                    $pipelineArray[] = [
                        '$sort' => [
                            'createdAt' => -1,
                        ]
                    ];
                    $pipelineArray[] = [
                        '$limit' => ($page + 1) * $perPage,
                    ];

                    $pipelineArray[] = [
                        '$addFields' => [
                            'alert_type' => Constants::METABASE,
                        ]
                    ];

                    $aggregationArray[] = [
                        '$unionWith' => [
                            'coll' => 'metabase_webhook_alerts',
                            "pipeline" => $pipelineArray
                        ]
                    ];
                }
                if ($showPrometheus) {
                    $pipelineArray = [];

                    if (!empty($alerts) || !empty($filterCreatedAtArray)) {
                        $matchAggregationArray = [];
                        if (!empty($alerts)) {
                            $matchAggregationArray['alertRuleId'] = ['$in' => $alerts];

                        }
                        if (!empty($filterCreatedAtArray)) {
                            $matchAggregationArray['createdAt'] = $filterCreatedAtArray;
                        }
                        $pipelineArray[] = [
                            '$match' => $matchAggregationArray
                        ];

                    }


                    $pipelineArray[] = [
                        '$sort' => [
                            'createdAt' => -1,
                        ]
                    ];
                    $pipelineArray[] = [
                        '$limit' => ($page + 1) * $perPage,
                    ];

                    $pipelineArray[] = [
                        '$addFields' => [
                            'alert_type' => Constants::PROMETHEUS,
                        ]
                    ];

                    $aggregationArray[] = [
                        '$unionWith' => [
                            'coll' => 'prometheus_histories',
                            "pipeline" => $pipelineArray
                        ]
                    ];

                }

                if ($showHealth) {
                    $pipelineArray = [];

                    if (!empty($alerts) || !empty($filterCreatedAtArray)) {
                        $matchAggregationArray = [];
                        if (!empty($alerts)) {
                            $matchAggregationArray['alertRuleId'] = ['$in' => $alerts];

                        }
                        if (!empty($filterCreatedAtArray)) {
                            $matchAggregationArray['createdAt'] = $filterCreatedAtArray;
                        }
                        $pipelineArray[] = [
                            '$match' => $matchAggregationArray
                        ];

                    }


                    $pipelineArray[] = [
                        '$sort' => [
                            'createdAt' => -1,
                        ]
                    ];
                    $pipelineArray[] = [
                        '$limit' => ($page + 1) * $perPage,
                    ];

                    $pipelineArray[] = [
                        '$addFields' => [
                            'alert_type' => Constants::HEALTH,
                        ]
                    ];

                    $aggregationArray[] = [
                        '$unionWith' => [
                            'coll' => 'health_histories',
                            "pipeline" => $pipelineArray
                        ]
                    ];


                }
                if ($showElastic) {

                    $pipelineArray = [];

                    if (!empty($alerts) || !empty($filterCreatedAtArray)) {
                        $matchAggregationArray = [];
                        if (!empty($alerts)) {
                            $matchAggregationArray['alertRuleId'] = ['$in' => $alerts];

                        }
                        if (!empty($filterCreatedAtArray)) {
                            $matchAggregationArray['createdAt'] = $filterCreatedAtArray;
                        }
                        $pipelineArray[] = [
                            '$match' => $matchAggregationArray
                        ];

                    }


                    $pipelineArray[] = [
                        '$sort' => [
                            'createdAt' => -1,
                        ]
                    ];
                    $pipelineArray[] = [
                        '$limit' => ($page + 1) * $perPage,
                    ];

                    $pipelineArray[] = [
                        '$addFields' => [
                            'alert_type' => Constants::ELASTIC,
                        ]
                    ];

                    $aggregationArray[] = [
                        '$unionWith' => [
                            'coll' => 'elastic_histories',
                            "pipeline" => $pipelineArray
                        ]
                    ];

                }

                if ($showZabbix) {

                    $pipelineArray = [];

                    if (!empty($alerts) || !empty($filterCreatedAtArray)) {
                        $matchAggregationArray = [];
                        if (!empty($alerts)) {
                            $matchAggregationArray['alertRuleId'] = ['$in' => $alerts];

                        }
                        if (!empty($filterCreatedAtArray)) {
                            $matchAggregationArray['createdAt'] = $filterCreatedAtArray;
                        }
                        $pipelineArray[] = [
                            '$match' => $matchAggregationArray
                        ];

                    }


                    $pipelineArray[] = [
                        '$sort' => [
                            'createdAt' => -1,
                        ]
                    ];
                    $pipelineArray[] = [
                        '$limit' => ($page + 1) * $perPage,
                    ];


                    $pipelineArray[] = [
                        '$addFields' => [
                            'alert_type' => Constants::ZABBIX,
                        ]
                    ];

                    $aggregationArray[] = [
                        '$unionWith' => [
                            'coll' => 'zabbix_webhook_alerts',
                            "pipeline" => $pipelineArray
                        ]
                    ];

                }
//                $aggregationArray[] = [
//                    '$unionWith' => [
//                        'coll' => 'grafana_webhook_alerts',
//                        "pipeline" => [
//                            [
//                                '$addFields' => [
//                                    'alert_type' => Constants::GRAFANA,
//                                ]
//                            ]
//                        ]
//                    ]
//                ];


                if (!$showApi) {
                    $aggregationArray[] = [
                        '$match' => [
                            'alert_type' => ['$not' => ['$eq' => Constants::API]],
                        ]
                    ];
                }

                $aggregationArray[] = [
                    '$sort' => [
                        'createdAt' => -1,
                    ]
                ];


                $aggregationArray[] = [
                    '$facet' => [
                        'metadata' => [['$count' => 'totalCount']],
                        "data" => [['$skip' => ($page - 1) * $perPage], ['$limit' => $perPage]],
                    ]
                ];


                return $collection->aggregate($aggregationArray);
            });
            $result = collect($query)->toArray()[0];
            $data = json_decode(json_encode(iterator_to_array($result['data'])), TRUE);
            if (!empty($data)) {
                $isEnd = json_decode(json_encode(iterator_to_array($result['metadata'])), TRUE)[0]['totalCount'] <= $perPage * $page;
                $data = collect($data)->map(function ($array) {
                    $array["id"] = $array['_id']['$oid'];
                    $array['createdAt'] = Carbon::createFromTimestampMs($array['createdAt']['$date']['$numberLong'])->format("Y-m-d H:i:s");
                    return $array;
                });
                return view("content.pages.alerts.all_history_ajax", compact("data", "isEnd", "page"));
            } else {
                return "";
            }
        } else {
            $from = $request->from ? Carbon::createFromTimestamp($request->from)->format("Y-m-d H:i") : "";
            $to = $request->to ? Carbon::createFromTimestamp($request->to)->format("Y-m-d H:i") : "";
            return view("content.pages.alerts.all_history", compact('alerts', 'from', 'to'));
        }

    }


    public static function HasAdminAccessAlert(User $user, AlertRule $alert)
    {
        if ($user->isAdmin()) return true;
        if ($user->_id == $alert->userId) return true;
        return false;
    }

    public static function HasUserAccessAlert(User $user, AlertRule $alert): bool
    {
        if (self::HasAdminAccessAlert($user, $alert)) return true;
        $userIds = $alert->userIds ?? [];
        if (in_array($user->_id, $userIds)) return true;
        return false;
    }

    public function getAlerts(AlertRuleType $type = null)
    {
        $tagsArray = ['alertRule'];
        $keyName = 'alertRule';
        if ($type) {
            $tagsArray[] = $type->value;
            $keyName .= ':' . $type->value;
        }

        return Cache::tags($tagsArray)->rememberForever($keyName,fn() => $this->getAlertsDB($type) );

    }

    public function getAlertsDB(AlertRuleType $type = null)
    {
        if ($type) {
            return AlertRule::where('type', $type)->get();
        } else
            return AlertRule::get();
    }

    public function flushCache(): void
    {
        Cache::tags(['alertRule'])->flush();
    }

    public function delete(AlertRule $alertRule)
    {
        $alertRuleId = $alertRule->_id;
        $type = $alertRule->type;
        $alertRule->delete();
        switch ($type) {
            case AlertRuleType::API:
            case AlertRuleType::NOTIFICATION:
                AlertInstance::where("alertRuleId", $alertRuleId)->delete();
                break;
            case AlertRuleType::PROMETHEUS:
                PrometheusCheck::where("alertRuleId", $alertRuleId)->delete();
                break;
            case AlertRuleType::ELASTIC:
                ElasticCheck::where("alertRuleId", $alertRuleId)->delete();
                break;
        }

    }

}
