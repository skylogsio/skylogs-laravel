<?php

namespace App\Services;

use App\Enums\AlertRuleType;
use App\Jobs\SendNotifyJob;
use App\Models\AlertInstance;
use App\Models\AlertRule;
use App\Models\ApiAlertHistory;
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
                               'created_at' => -1,
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
                        $matchAggregationArray['created_at'] = $filterCreatedAtArray;
                    }
                    $aggregationArray[] = [
                        '$match' => $matchAggregationArray
                    ];

                }

                $aggregationArray[] = [
                    '$sort' => [
                        'created_at' => -1,
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
                            $matchAggregationArray['alert_rule_id'] = ['$in' => $alerts];

                        }
                        if (!empty($filterCreatedAtArray)) {
                            $matchAggregationArray['created_at'] = $filterCreatedAtArray;
                        }
                        $pipelineArray[] = [
                            '$match' => $matchAggregationArray
                        ];

                    }

                    $pipelineArray[] = [
                        '$sort' => [
                            'created_at' => -1,
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
                            $matchAggregationArray['alert_rule_id'] = ['$in' => $alerts];

                        }
                        if (!empty($filterCreatedAtArray)) {
                            $matchAggregationArray['created_at'] = $filterCreatedAtArray;
                        }
                        $pipelineArray[] = [
                            '$match' => $matchAggregationArray
                        ];

                    }

                    $pipelineArray[] = [
                        '$sort' => [
                            'created_at' => -1,
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
                            $matchAggregationArray['alert_rule_id'] = ['$in' => $alerts];

                        }
                        if (!empty($filterCreatedAtArray)) {
                            $matchAggregationArray['created_at'] = $filterCreatedAtArray;
                        }
                        $pipelineArray[] = [
                            '$match' => $matchAggregationArray
                        ];

                    }


                    $pipelineArray[] = [
                        '$sort' => [
                            'created_at' => -1,
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
                            $matchAggregationArray['alert_rule_id'] = ['$in' => $alerts];

                        }
                        if (!empty($filterCreatedAtArray)) {
                            $matchAggregationArray['created_at'] = $filterCreatedAtArray;
                        }
                        $pipelineArray[] = [
                            '$match' => $matchAggregationArray
                        ];

                    }


                    $pipelineArray[] = [
                        '$sort' => [
                            'created_at' => -1,
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
                            $matchAggregationArray['alert_rule_id'] = ['$in' => $alerts];

                        }
                        if (!empty($filterCreatedAtArray)) {
                            $matchAggregationArray['created_at'] = $filterCreatedAtArray;
                        }
                        $pipelineArray[] = [
                            '$match' => $matchAggregationArray
                        ];

                    }


                    $pipelineArray[] = [
                        '$sort' => [
                            'created_at' => -1,
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
                            $matchAggregationArray['alert_rule_id'] = ['$in' => $alerts];

                        }
                        if (!empty($filterCreatedAtArray)) {
                            $matchAggregationArray['created_at'] = $filterCreatedAtArray;
                        }
                        $pipelineArray[] = [
                            '$match' => $matchAggregationArray
                        ];

                    }


                    $pipelineArray[] = [
                        '$sort' => [
                            'created_at' => -1,
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
                        'created_at' => -1,
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
//            ds(json_decode(json_encode(iterator_to_array($result['metadata'])), TRUE)[0]);
            $data = json_decode(json_encode(iterator_to_array($result['data'])), TRUE);
//            ds($data);
            if (!empty($data)) {
                $isEnd = json_decode(json_encode(iterator_to_array($result['metadata'])), TRUE)[0]['totalCount'] <= $perPage * $page;
//            ds(\Arr::dot($data[0]['created_at']));
                $data = collect($data)->map(function ($array) {
                    $array["id"] = $array['_id']['$oid'];
                    $array['created_at'] = Carbon::createFromTimestampMs($array['created_at']['$date']['$numberLong'])->format("Y-m-d H:i:s");
//                ds($array);
                    return $array;
                });
                return view("content.pages.alerts.all_history_ajax", compact("data", "isEnd", "page"));
            } else {
                return "";
            }
//            ds(\Arr::dot($data[0]));
        } else {
            $from = $request->from ? Carbon::createFromTimestamp($request->from)->format("Y-m-d H:i") : "";
            $to = $request->to ? Carbon::createFromTimestamp($request->to)->format("Y-m-d H:i") : "";
            return view("content.pages.alerts.all_history", compact('alerts', 'from', 'to'));
        }

    }


    public static function HasAdminAccessAlert(User $user, AlertRule $alert)
    {
        if ($user->isAdmin()) return true;
        if ($user->_id == $alert->user_id) return true;
        return false;
    }

    public static function HasUserAccessAlert(User $user, AlertRule $alert): bool
    {
        if (self::HasAdminAccessAlert($user, $alert)) return true;
        $userIds = $alert->user_ids ?? [];
        if (in_array($user->_id, $userIds)) return true;
        return false;
    }

    public static function GetAlerts(AlertRuleType $type = null)
    {
        $tagsArray = ['alert_rule'];
        $keyName = 'alert_rule';
        if ($type) {
            $tagsArray[] = $type->value;
            $keyName .= ':' . $type->value;
        }

        return Cache::tags($tagsArray)->rememberForever($keyName, function () use ($type) {
            if ($type) {
                return AlertRule::where('type', $type)->get();
            } else
                return AlertRule::get();
        });
    }

    public static function GetAlertsDB(AlertRuleType $type = null)
    {
        if ($type) {
            return AlertRule::where('type', $type)->get();
        } else
            return AlertRule::get();
    }

    public static function FlushAlertRuleCache(): void
    {
        Cache::tags(['alert_rule'])->flush();
    }

}
