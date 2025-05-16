<?php

namespace App\Services;

use App\Helpers\Utilities;
use App\Jobs\SendNotifyJob;
use App\Models\AlertRule;
use App\Models\GrafanaWebhookAlert;
use App\Helpers\Constants;

class GrafanaService
{

    public static function CheckAlertFilter($alert, $query)
    {

        switch ($query['token']['type']) {
            case Constants::LITERAL:
                list($key, $patterns) = explode(":", $query['token']['literal']);
                $key = trim($key);
                $patterns = trim($patterns);
                if ($key == "grafana_instance") {
                    return Utilities::CheckPatternsString($patterns, $alert['instance']);
                } elseif ((!empty($alert['labels'][$key]) && Utilities::CheckPatternsString($patterns, $alert['labels'][$key]))) {
                    return true;
                } elseif ((!empty($alert['annotations'][$key]) && Utilities::CheckPatternsString($patterns, $alert['annotations'][$key]))) {
                    return true;
                }
                return false;
            case Constants::AND:
                $right = self::CheckAlertFilter($alert, $query['right']);
                $left = self::CheckAlertFilter($alert, $query['left']);
                return ($right && $left);
            case Constants::OR:
                $right = self::CheckAlertFilter($alert, $query['right']);
                $left = self::CheckAlertFilter($alert, $query['left']);
                return ($right || $left);
            case Constants::XOR:
                $right = self::CheckAlertFilter($alert, $query['right']);
                $left = self::CheckAlertFilter($alert, $query['left']);
                return ($right xor $left);
            case Constants::NOT:
                return !self::CheckAlertFilter($alert, $query['right']);


        }

    }

    public static function CheckMatchedAlerts($webhook, $alerts, $alertRules,): array
    {

        $status = $webhook['status'];
        $fireAlertsByRule = [];
        foreach ($alerts as $alert) {
            foreach ($alertRules as $alertRule) {
                $isMatch = true;
                $matchLabels = [];
                $matchAnnotations = [];
                if (empty($alertRule["queryType"]) || $alertRule['queryType'] == AlertRule::DYNAMIC_QUERY_TYPE) {

                    if (in_array($alert['dataSourceId'], $alertRule['dataSourceIds']) && $alert['labels']['alertname'] == $alertRule['dataSourceAlertName']) {

                        if (!empty($alertRule->extraField))
                            foreach ($alertRule->extraField as $key => $patterns) {
                                if ((!empty($alert['labels'][$key]) && Utilities::CheckPatternsString($patterns, $alert['labels'][$key]))) {
                                    $matchLabels[$key] = $patterns;
                                } elseif ((!empty($alert['annotations'][$key]) && Utilities::CheckPatternsString($patterns, $alert['annotations'][$key]))) {
                                    $matchAnnotations[$key] = $patterns;
                                } else {
                                    $isMatch = false;
                                    break;
                                }
                            }

                    } else {
                        $isMatch = false;
                    }

                } else {
                    // TEXT QUERY

                    if (!empty($alertRule->queryObject)) {
                        $matchedFilterResult = self::CheckAlertFilter($alert, $alertRule->queryObject);
                        if (!$matchedFilterResult) {
                            $isMatch = false;
                        }
                    }


                }


                if ($isMatch) {
                    // check with database checkprometheus

                    if (empty($fireAlertsByRule[$alertRule->_id])) {
                        $fireAlertsByRule[$alertRule->_id] = [];
                    }

                    $fireAlertsByRule[$alertRule->_id][] = [
                        "dataSourceId" => $alert['dataSourceId'],
                        "alertRuleName" => $alertRule->name,
                        "dataSourceAlertName" => $alert['labels']['alertname'],
                        "labels" => $alert['labels'],
                        "annotations" => $alert['annotations'],
                        "alertRuleId" => $alertRule->_id,
//                        "state" => $status,
                    ];

                }

            }
        }
        return $fireAlertsByRule;

    }

    public static function SaveMatchedAlerts($dataSource,$webhook, $matchedAlerts)
    {
        $status = $webhook['status'];
        foreach ($matchedAlerts as $alertRuleId => $alerts) {
            $model = new GrafanaWebhookAlert();
            $model->alerts = $alerts;
            $model->dataSourceId = $dataSource->id;
            $model->dataSourceName = $dataSource->name;
            $model->alertRuleId = $alertRuleId;
            $model->status = $status;

            if(!empty($webhook['groupLabels'])){
                $model->groupLabels = $webhook['groupLabels'];
            }
            if(!empty($webhook['commonLabels'])){
                $model->commonLabels = $webhook['commonLabels'];
            }
            if(!empty($webhook['commonAnnotations'])){
                $model->commonAnnotations = $webhook['commonAnnotations'];
            }
            if(!empty($webhook['externalURL'])){
                $model->externalURL = $webhook['externalURL'];
            }
            if(!empty($webhook['groupKey'])){
                $model->groupKey = $webhook['groupKey'];
            }
            if(!empty($webhook['truncatedAlerts'])){
                $model->truncatedAlerts = $webhook['truncatedAlerts'];
            }
            if(!empty($webhook['orgId'])){
                $model->orgId = $webhook['orgId'];
            }
            if(!empty($webhook['title'])){
                $model->title = $webhook['title'];
            }
            if(!empty($webhook['message'])){
                $model->message = $webhook['message'];
            }

            $alertRule = $model->alertRule;

            if ($alertRule) {
                if ($status == GrafanaWebhookAlert::RESOLVED)
                    $alertRule->state = AlertRule::RESOlVED;
                elseif ($status == GrafanaWebhookAlert::FIRING)
                    $alertRule->state = AlertRule::CRITICAL;
                $alertRule->save();
            }
            $model->save();

            SendNotifyService::CreateNotify(SendNotifyJob::GRAFANA_WEBHOOK, $model, $alertRule->_id);

        }

    }

}
