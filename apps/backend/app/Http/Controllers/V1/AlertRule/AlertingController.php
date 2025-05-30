<?php

namespace App\Http\Controllers\V1\AlertRule;


use App\Enums\AlertRuleType;
use App\Enums\Constants;
use App\Enums\DataSourceType;

use App\Http\Controllers\Controller;
use App\Jobs\SendNotifyJob;
use App\Models\AlertInstance;
use App\Models\AlertRule;
use App\Models\ApiAlertHistory;
use App\Models\DataSource\DataSource;
use App\Models\ElasticHistory;
use App\Models\Endpoint;
use App\Models\GrafanaInstance;
use App\Models\GrafanaWebhookAlert;
use App\Models\GroupAlertRule;
use App\Models\HealthCheck;
use App\Models\ElasticCheck;
use App\Models\HealthHistory;
use App\Models\MetabaseWebhookAlert;
use App\Models\PrometheusCheck;
use App\Models\PrometheusHistory;
use App\Models\PrometheusInstance;
use App\Models\SentryWebhookAlert;
use App\Models\Service;

use App\Models\User;
use App\Models\ZabbixWebhookAlert;
use App\Services\AlertRuleService;
use App\Services\DataSourceService;
use App\Services\GrafanaInstanceService;
use App\Services\PrometheusInstanceService;
use App\Services\SendNotifyService;

use App\Services\TagService;
use Carbon\Carbon;
use Excel;
use Illuminate\Console\View\Components\Alert;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\MessageBag;
use MongoDB\BSON\UTCDateTime;

class AlertingController extends Controller
{


    public function Index(Request $request)
    {

        $perPage = $request->per_page ?? 25;

        $data = AlertRule::latest();

        $currentUser = \Auth::user();

        if (!$currentUser->hasRole("admin")) {
            $data = $data->where(function ($query) use ($request) {
                return $query->where('user_id', \Auth::id())->orWhereIn("user_ids", [\Auth::user()->_id]);
            });
        }


        if ($request->has("alertname") && !empty($request->alertname)) {
            $data = $data->where("alertname", "like", '%' . $request->alertname . '%');
        }

        if ($request->has("types") && !empty($request->types)) {
            $data = $data->whereIn("type", $request->types);
        }

        if ($request->has("endpointId") && !empty($request->endpointId)) {
            $endpointId = $request->endpointId;

            $data = $data->whereIn("endpoint_ids", [$endpointId]);

        }

        $data = $data->paginate($perPage);

        foreach ($data as &$alert) {
            $alert->hasAdminAccess = AlertRuleService::HasAdminAccessAlert($currentUser, $alert);
            $alert->has_admin_access = AlertRuleService::HasAdminAccessAlert($currentUser, $alert);
            $alert->status_label = $alert->getStatus();
            $alert->is_silent = $alert->isSilent();
        }

        return response()->json($data);


    }

    public function FilterEndpoints(Request $request){

        $adminUserId = User::where('username', 'admin')->first()->_id;

        $currentUser = \Auth::user();

        if ($currentUser->hasRole("admin")) {
            $selectableEndpoints = Endpoint::get();
        } else {
            $selectableEndpoints = Endpoint::whereIn("user_id", [$adminUserId, $currentUser->_id])->get();
        }

        return response()->json($selectableEndpoints);
    }
    public function GetTypes(Request $request){
        return response()->json(AlertRuleType::GetTypes());
    }
    public function Silent(Request $request, $id)
    {
        $alert = AlertRule::where("_id", $id)->first();
        if ($alert->isSilent()) {
            $alert->unSilent();
        } else {
            $alert->silent();
        }

        return ['status' => true];
    }

    public function CreateData(Request $request)
    {
        $prometheusDataSources = DataSource::where("type", DataSourceType::PROMETHEUS)->get()->pluck("name");
        $grafanaDataSources = DataSource::where("type", DataSourceType::GRAFANA)->get()->pluck("name");
        $splunkDataSources = DataSource::where("type", DataSourceType::SPLUNK)->get()->pluck("name");

        $adminUserId = User::where('username', 'admin')->first()->_id;

        $endpoints = Endpoint::whereIn("user_id", [$adminUserId, \Auth::user()->_id])->get();
        $users = User::whereNotIn("_id", [$adminUserId, \Auth::id()])->get();

        return response()->json(
            compact(
                "prometheusDataSources",
                "grafanaDataSources",
                "endpoints",
                'splunkDataSources',
                "users"
            )
        );

    }

    public function Store(Request $request)
    {


        $va = \Validator::make(
            $request->all(),
            [
                'name' => "required|unique:alert_rules",
                'type' => "required",
            ], [
            ]
        );
//        dd("");
        if ($va->passes()) {
            $alertType = AlertRuleType::tryFrom($request->type);

            switch ($alertType) {
                case AlertRuleType::GRAFANA:

                    $alert = AlertRule::create([
//                        'instance' => $request->grafana_instance,
                        'name' => $request->name,
                        'interval' => ((int)$request->interval),
//                        'grafana_alertname' => $request->grafana_alert,
                        'type' => $request->type,
                        "user_id" => \Auth::id()
                    ]);
                    $alert->queryType = $request->grafanaQueryType;

                    if ($alert->queryType == AlertRule::DYNAMIC_QUERY_TYPE) {
                        $alert->instance = empty($request->grafana_instance) ? [] : array_unique($request->grafana_instance);
                        $alert->grafana_alertname = $request->grafana_alert;
                        $extraFields = [];
                        if ($request->has("extraField") && !empty($request->extraField))
                            foreach ($request->extraField as $value) {
                                if (!empty($value)) {
                                    if (!empty($value["key"]) && !empty($value["value"]))
                                        $extraFields[$value["key"]] = $value['value'];
                                }
                            }
                        $alert->extraField = $extraFields;
                    } else {
                        $alert->prometheus_query = $request->prometheus_query;
                        $alert->prometheus_query_object = $request->prometheus_query_object;
                    }
                    $alert->save();

                    break;
                case AlertRuleType::PROMETHEUS:
                    $alert = AlertRule::create([
//                        'instance' => $request->prometheus_instance,
                        'name' => $request->name,
                        'interval' => ((int)$request->interval),
//                        'prometheus_alertname' => $request->prometheus_alert,
                        'type' => $request->type,
                        "user_id" => \Auth::id()
                    ]);
                    $alert->queryType = $request->prometheusQueryType;
                    if ($alert->queryType == AlertRule::DYNAMIC_QUERY_TYPE) {
                        $alert->instance = array_unique($request->prometheus_instance);
                        $alert->prometheus_alertname = $request->prometheus_alert;
                        $extraFields = [];
                        if ($request->has("extraField") && !empty($request->extraField))
                            foreach ($request->extraField as $value) {
                                if (!empty($value)) {
                                    if (!empty($value["key"]) && !empty($value["value"]))
                                        $extraFields[$value["key"]] = $value['value'];
                                }
                            }
                        $alert->extraField = $extraFields;
                    } else {
                        $alert->prometheus_query = $request->prometheus_query;
                        $alert->prometheus_query_object = $request->prometheus_query_object;
                    }
//                    $alert->extraField = $extraFields;
                    $alert->save();

                    break;
                case AlertRuleType::SENTRY:
                case AlertRuleType::METABASE:
                case AlertRuleType::ZABBIX:

                    $alert = AlertRule::create([
                        'name' => $request->name,
                        'type' => $request->type,
                        'interval' => ((int)$request->interval),
                        "user_id" => \Auth::id()
                    ]);
                    break;
                case AlertRuleType::SPLUNK:

                    $alert = AlertRule::create([
                        'name' => $request->name,
                        "splunk_alertname" => $request->splunk_alertname,
                        'type' => AlertRuleType::SPLUNK,
                        "user_id" => \Auth::id()
                    ]);
                    break;
                case AlertRuleType::NOTIFICATION:

                    $alert = AlertRule::create([
                        'name' => $request->name,
                        'type' => $request->type,
                        "user_id" => \Auth::id()
                    ]);
                    break;
                case AlertRuleType::API:

                    $enableAutoResolve = !empty($request->enableAutoResolve) && $request->enableAutoResolve;

                    $autoResolveMinutes = 0;
                    if ($enableAutoResolve) {
                        $autoResolveMinutes = ((int)$request->autoResolveMinutes);
                    }
                    $alert = AlertRule::create([
                        'name' => $request->name,
                        'type' => $request->type,
                        "user_id" => \Auth::id(),
                        "enableAutoResolve" => $enableAutoResolve,
                        "autoResolveMinutes" => $autoResolveMinutes,
                    ]);
                    break;
                case AlertRuleType::HEALTH:

                    $alert = AlertRule::create([
                        'name' => $request->name,
                        'type' => $request->type,
                        'interval' => ((int)$request->interval),
                        "user_id" => \Auth::id(),
                        "url" => $request->url,
                        "threshold_down" => ((int)$request->threshold_down),
                        "threshold_up" => ((int)$request->threshold_up),
                        "basic_auth_username" => $request->username ?? "",
                        "basic_auth_password" => $request->password ?? "",
                    ]);
                    break;
                case AlertRuleType::ELASTIC:

                    $alert = AlertRule::create([
                        'name' => $request->name,
                        'type' => $request->type,
                        'interval' => ((int)$request->interval),
                        "user_id" => \Auth::id(),
                        "dataview_name" => $request->dataview_name,
                        "dataview_title" => $request->dataview_title,
                        "query_string" => $request->query_string,
                        "minutes" => ((int)$request->minutes),
                        "conditionType" => $request->conditionType,
                        "count_document" => ((int)$request->count_document),
                    ]);
                    break;
            }

            $alert->save();

            if ($request->has("endpoints") && !empty($request->endpoints))
                foreach ($request->endpoints as $end) {
                    $alert->push("endpoint_ids", $end, true);
                }

            if ($request->has("accessUsers") && !empty($request->accessUsers))
                foreach ($request->accessUsers as $userId) {
                    $alert->push("user_ids", $userId, true);
                }


            return ['status' => true];
        } else {
            return ['status' => false,"message" => $va->messages()];
        }
    }

    public function Show(Request $request, $id)
    {
        $alert = AlertRule::where("_id", $id)->firstOrFail();
        $userIds = [];
        if (!empty($alert->user_ids)) {
            $userIds = $alert->user_ids;
        }
        $userIds[] = $alert->user_id;

        if (!(Auth::user()->hasRole("admin") || in_array(Auth::user()->_id, $userIds))) {
            abort(403);
        }


        return response()->json($alert);
    }

    public function AllHistory(Request $request)
    {
        return AlertRuleService::GetAllHistory($request);

    }

    public function History(Request $request, $id)
    {
        if ($request->has("perPage")) {
            $perPage = $request->perPage;
        } else
            $perPage = 50;

        $pageConfigs = ['myLayout' => 'blank'];

        $alert = AlertRule::where("_id", $id)->firstOrFail();
        $userIds = [];
        if (!empty($alert->user_ids)) {
            $userIds = $alert->user_ids;
        }
        $userIds[] = $alert->user_id;

        if (!(Auth::user()->hasRole("admin") || in_array(Auth::user()->_id, $userIds))) {
            abort(403);
        }

        switch ($alert->type) {
            case AlertRuleType::GRAFANA:
                if ($request->ajax()) {
                    $data = GrafanaWebhookAlert::where("alert_rule_id", $id)->latest();
                    if ($request->has("from") && !empty($request->from)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->from);
                        $data = $data->where("created_at", ">=", $date->toDateTime());
                    }
                    if ($request->has("to") && !empty($request->to)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->to);
                        $data = $data->where("created_at", "<=", $date->toDateTime());
                    }
                    $data = $data->paginate($perPage);
                    return view("content.pages.alerts.history_grafana_ajax", compact("alert", "data"));
                } else
                    return view("content.pages.alerts.history_grafana", compact("alert", "pageConfigs"));

            case AlertRuleType::PROMETHEUS:
                if ($request->ajax()) {
                    $data = PrometheusHistory::where("alert_rule_id", $id)->latest();
                    if ($request->has("from") && !empty($request->from)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->from);
                        $data = $data->where("created_at", ">=", $date->toDateTime());
                    }
                    if ($request->has("to") && !empty($request->to)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->to);
                        $data = $data->where("created_at", "<=", $date->toDateTime());
                    }
                    $data = $data->paginate($perPage);
                    return view("content.pages.alerts.history_prometheus_ajax", compact("alert", "data"));
                } else
                    return view("content.pages.alerts.history_prometheus", compact("alert", "pageConfigs"));

            case AlertRuleType::SENTRY:
                if ($request->ajax()) {
                    $data = SentryWebhookAlert::where("alert_rule_id", $id)->latest();
                    if ($request->has("from") && !empty($request->from)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->from);
                        $data = $data->where("created_at", ">=", $date->toDateTime());
                    }
                    if ($request->has("to") && !empty($request->to)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->to);
                        $data = $data->where("created_at", "<=", $date->toDateTime());
                    }
                    $data = $data->paginate($perPage);
                    return view("content.pages.alerts.history_sentry_ajax", compact("alert", "data"));
                } else
                    return view("content.pages.alerts.history_sentry", compact("alert", "pageConfigs"));

            case AlertRuleType::SPLUNK:
                if ($request->ajax()) {
                    $data = SplunkWebhookAlert::where("alert_rule_id", $id)->latest();
                    if ($request->has("from") && !empty($request->from)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->from);
                        $data = $data->where("created_at", ">=", $date->toDateTime());
                    }
                    if ($request->has("to") && !empty($request->to)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->to);
                        $data = $data->where("created_at", "<=", $date->toDateTime());
                    }
                    $data = $data->paginate($perPage);
                    return view("content.pages.alerts.history_splunk_ajax", compact("alert", "data"));
                } else
                    return view("content.pages.alerts.history_splunk", compact("alert", "pageConfigs"));

            case AlertRuleType::METABASE:
                if ($request->ajax()) {
                    $data = MetabaseWebhookAlert::where("alert_rule_id", $id)->latest();
                    if ($request->has("from") && !empty($request->from)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->from);
                        $data = $data->where("created_at", ">=", $date->toDateTime());
                    }
                    if ($request->has("to") && !empty($request->to)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->to);
                        $data = $data->where("created_at", "<=", $date->toDateTime());
                    }
                    $data = $data->paginate($perPage);
                    return view("content.pages.alerts.history_metabase_ajax", compact("alert", "data"));
                } else
                    return view("content.pages.alerts.history_metabase", compact("alert", "pageConfigs"));

            case AlertRuleType::ZABBIX:
                if ($request->ajax()) {
                    $data = ZabbixWebhookAlert::where("alert_rule_id", $id)->latest();
                    if ($request->has("from") && !empty($request->from)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->from);
                        $data = $data->where("created_at", ">=", $date->toDateTime());
                    }
                    if ($request->has("to") && !empty($request->to)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->to);
                        $data = $data->where("created_at", "<=", $date->toDateTime());
                    }
                    $data = $data->paginate($perPage);
                    return view("content.pages.alerts.history_zabbix_ajax", compact("alert", "data"));
                } else
                    return view("content.pages.alerts.history_zabbix", compact("alert", "pageConfigs"));

            case AlertRuleType::API:
                if ($request->ajax()) {
                    $data = ApiAlertHistory::where("alertRule_id", $id)->latest();
                    if ($request->has("from") && !empty($request->from)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->from);
                        $data = $data->where("created_at", ">=", $date->toDateTime());
                    }
                    if ($request->has("to") && !empty($request->to)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->to);
                        $data = $data->where("created_at", "<=", $date->toDateTime());
                    }
                    $data = $data->paginate($perPage);
                    return view("content.pages.alerts.history_api_ajax", compact("alert", "data"));
                } else
                    return view("content.pages.alerts.history_api", compact("alert", "pageConfigs"));

            case AlertRuleType::NOTIFICATION:
                if ($request->ajax()) {
                    $data = ApiAlertHistory::where("alertRule_id", $id)->latest();
                    if ($request->has("from") && !empty($request->from)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->from);
                        $data = $data->where("created_at", ">=", $date->toDateTime());
                    }
                    if ($request->has("to") && !empty($request->to)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->to);
                        $data = $data->where("created_at", "<=", $date->toDateTime());
                    }
                    $data = $data->paginate($perPage);
                    return view("content.pages.alerts.history_notification_ajax", compact("alert", "data"));
                } else
                    return view("content.pages.alerts.history_notification", compact("alert", "pageConfigs"));

            case AlertRuleType::HEALTH:
                if ($request->ajax()) {
                    $data = HealthHistory::where("alert_rule_id", $id)->latest();
                    if ($request->has("from") && !empty($request->from)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->from);
                        $data = $data->where("created_at", ">=", $date->toDateTime());
                    }
                    if ($request->has("to") && !empty($request->to)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->to);
                        $data = $data->where("created_at", "<=", $date->toDateTime());
                    }
                    $data = $data->paginate($perPage);
                    return view("content.pages.alerts.history_health_ajax", compact("alert", "data"));
                } else
                    return view("content.pages.alerts.history_health", compact("alert", "pageConfigs"));

            case AlertRuleType::ELASTIC:
                if ($request->ajax()) {
                    $data = ElasticHistory::where("alert_rule_id", $id)->latest();
                    if ($request->has("from") && !empty($request->from)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->from);
                        $data = $data->where("created_at", ">=", $date->toDateTime());
                    }
                    if ($request->has("to") && !empty($request->to)) {
                        $date = Carbon::createFromFormat("Y-m-d H:i", $request->to);
                        $data = $data->where("created_at", "<=", $date->toDateTime());
                    }
                    $data = $data->paginate($perPage);
                    return view("content.pages.alerts.history_elastic_ajax", compact("alert", "data"));
                } else
                    return view("content.pages.alerts.history_elastic", compact("alert", "pageConfigs"));
        }

        return null;
    }

    public function Update(Request $request, $id)
    {
        $model = AlertRule::where("_id", $id)->firstOrFail();

        $currentUser = Auth::user();
        $access = AlertRuleService::HasAdminAccessAlert($currentUser, $model);

        if (!$access) {
            abort(403);
        }

        $prometheusAlerts = [];
        $grafanaAlerts = [];

        switch ($model->type) {
            case AlertRuleType::GRAFANA:
                $grafanaInstances = GrafanaInstance::pluck("name");

                $r = GrafanaInstanceService::getRules($model->instance);

                foreach ($r as $value) {
                    $grafanaAlerts[] = $value->title;
                }

                return view('content.pages.alerts.modal_update_grafana',
                    compact(
                        "grafanaInstances",
                        "grafanaAlerts",
                        "model"
                    ));
            case AlertRuleType::PROMETHEUS:
                $prometheusInstances = PrometheusInstance::pluck("name");

                $r = PrometheusInstanceService::getRules();
                foreach ($r as $value) {
                    $prometheusAlerts[] = $value->name;
                }

                return view('content.pages.alerts.modal_update_prometheus',
                    compact(
                        "prometheusInstances",
                        "prometheusAlerts",
                        "model"
                    ));
            case AlertRuleType::SENTRY:
            case AlertRuleType::METABASE:
            case AlertRuleType::ZABBIX:
                return view("content.pages.alerts.modal_update_sentry", compact("model"));
            case AlertRuleType::SPLUNK:
                $splunkAlerts = SplunkService::GetAlerts();
                return view("content.pages.alerts.modal_update_splunk", compact("model", 'splunkAlerts'));
            case AlertRuleType::NOTIFICATION:
                return view("content.pages.alerts.modal_update_notification", compact("model"));
            case AlertRuleType::API:
                return view("content.pages.alerts.modal_update_api", compact("model"));
            case AlertRuleType::HEALTH:
                return view("content.pages.alerts.modal_update_health", compact("model"));
            case AlertRuleType::ELASTIC:
                return view("content.pages.alerts.modal_update_elastic", compact("model"));
        }
        abort(404);

    }


    public function StoreUpdate(Request $request, $id)
    {
        $model = AlertRule::where("_id", $id)->firstOrFail();
        switch ($model->type) {
            case AlertRuleType::GRAFANA:
                $extraFields = [];
                $model->queryType = $request->queryType;
                if ($request->queryType == AlertRule::DYNAMIC_QUERY_TYPE) {
                    $model->instance = array_unique($request->prometheus_instance);
                    if ($request->has("extraField") && !empty($request->extraField))
                        foreach ($request->extraField as $value) {
                            if (!empty($value)) {
                                if (!empty($value["key"]) && !empty($value["value"]))
                                    $extraFields[$value["key"]] = $value['value'];
                            }
                        }
                    $model->extraField = $extraFields;
                } else {
                    $model->prometheus_query = $request->prometheus_query;
                    $model->prometheus_query_object = $request->prometheus_query_object;
                }

                $model->interval = ((int)$request->interval);
                $model->state = null;
                $model->save();
                break;
            case AlertRuleType::PROMETHEUS:

                $extraFields = [];
                $model->queryType = $request->queryType;
                if ($request->queryType == AlertRule::DYNAMIC_QUERY_TYPE) {
                    $model->instance = array_unique($request->prometheus_instance);
                    if ($request->has("extraField") && !empty($request->extraField))
                        foreach ($request->extraField as $value) {
                            if (!empty($value)) {
                                if (!empty($value["key"]) && !empty($value["value"]))
                                    $extraFields[$value["key"]] = $value['value'];
                            }
                        }
                    $model->extraField = $extraFields;
                } else {
                    $model->prometheus_query = $request->prometheus_query;
                    $model->prometheus_query_object = $request->prometheus_query_object;
                }
                $model->interval = ((int)$request->interval);
                $model->state = null;
                $model->save();
                break;
            case AlertRuleType::METABASE:
                $model->alertname = $request->alertname;
                $model->interval = ((int)$request->interval);
                $model->state = null;
                $model->save();
                break;
            case AlertRuleType::SENTRY:
                $model->alertname = $request->alertname;
                $model->interval = ((int)$request->interval);
                $model->state = null;
                $model->save();
                break;
            case AlertRuleType::SPLUNK:
                $model->alertname = $request->alertname;
                $model->splunk_alertname = $request->splunk_alertname;
                $model->interval = ((int)$request->interval);
                $model->state = null;
                $model->save();
                break;
            case AlertRuleType::ZABBIX:
                $model->alertname = $request->alertname;
                $model->interval = ((int)$request->interval);
                $model->state = null;
                $model->save();
                break;
            case AlertRuleType::ELASTIC:
                $model->alertname = $request->alertname;
                $model->interval = ((int)$request->interval);
                $model->dataview_name = $request->dataview_name;
                $model->dataview_title = $request->dataview_title;
                $model->query_string = $request->query_string;
                $model->conditionType = $request->conditionType;
                $model->minutes = ((int)$request->minutes);
                $model->count_document = ((int)$request->count_document);
                $model->save();
                ElasticCheck::where("alert_rule_id", $model->_id)->delete();
                break;
            case AlertRuleType::HEALTH:
                $model->alertname = $request->alertname;
                $model->interval = ((int)$request->interval);
                $model->threshold_down = ((int)$request->threshold_down);
                $model->threshold_up = ((int)$request->threshold_up);
                $model->url = $request->url;
                $model->basic_auth_username = $request->username;
                $model->basic_auth_password = $request->password;
                $model->save();
                HealthCheck::where("alert_rule_id", $model->_id)->delete();
                break;
            case AlertRuleType::NOTIFICATION:
                AlertInstance::where("alertname", $model->alertname)->update(['name' => $request->alertname]);
                $model->alertname = $request->alertname;
                $model->interval = ((int)$request->interval);
                $model->public = $request->public;
                $model->save();
                break;
            case AlertRuleType::API:
                AlertInstance::where("alertname", $model->alertname)->update(['name' => $request->alertname]);
                $autoResolveMinutes = 0;
                $enableAutoResolve = !empty($request->enableAutoResolve) && $request->enableAutoResolve;
                if ($enableAutoResolve) {
                    $autoResolveMinutes = ((int)$request->autoResolveMinutes);
                }
                $model->enableAutoResolve = $enableAutoResolve;
                $model->autoResolveMinutes = $autoResolveMinutes;
                $model->name = $request->name;
                $model->save();
                break;
        }
        return response()->json(['status'=>true]);

    }

    public function ResolveAlert(Request $request, $id)
    {

        $alert = AlertRule::where('_id', $id)->first();
        $sendResolve = false;
        $currentUser = auth()->user();
        if (!AlertRuleService::HasUserAccessAlert($currentUser, $alert)) {
            abort(403);
        }

        switch ($alert->type) {
            case AlertRuleType::API:
                $apiAlerts = AlertInstance::where("alertname", $alert->alertname)->where("state", AlertInstance::FIRE)->get();
                if ($apiAlerts->isNotEmpty()) {
                    $sendResolve = true;
                    foreach ($apiAlerts as $apiAlert) {
                        $apiAlert->description = "";
                        $apiAlert->file = "";
                        $apiAlert->fileName = "";
                        $apiAlert->state = AlertInstance::RESOLVED;
                        $apiAlert->save();
                        $apiHistory = $apiAlert->createHistory();
                        $apiAlert->createStatusHistory($apiHistory);
                    }
                }
                break;
            case AlertRuleType::SENTRY:
                if (empty($alert->state) || $alert->state != AlertRule::RESOlVED) {
                    $sendResolve = true;
                    $alert->state = AlertRule::RESOlVED;
                    $alert->save();

                    SentryWebhookAlert::create([
                        "alert_name" => $alert->name,
                        "alert_rule_id" => $alert->_id,
                        "action" => "resolved",
                        "message" => "resolved manually.",
                        "description" => "resolved manually.",
                    ]);
                }
                break;
            case AlertRuleType::ZABBIX:
                if (empty($alert->status) || $alert->status != AlertRule::RESOlVED) {
                    $sendResolve = true;
                    $alert->status = AlertRule::RESOlVED;
                    $alert->save();
                }
                break;
            case AlertRuleType::PROMETHEUS:
                $prometheusAlert = PrometheusCheck::where("alert_rule_id", $alert->_id)->where("state", PrometheusCheck::FIRE)->first();
                if ($prometheusAlert && $prometheusAlert->state == PrometheusCheck::FIRE) {
                    $prometheusAlert->state = PrometheusCheck::RESOLVED;
                    $prometheusAlert->save();
                    $prometheusAlert->createHistory();
                    $sendResolve = true;
                }
                break;
            case AlertRuleType::HEALTH:
                $check = HealthCheck::where("alert_rule_id", $alert->_id)->where("state", HealthCheck::DOWN)->first();
                if ($check && $check->state == HealthCheck::DOWN) {
                    $check->state = HealthCheck::UP;
                    $check->counter = 0;
                    $check->save();

                    $sendResolve = true;
                }
                break;
            case AlertRuleType::ELASTIC:
                $check = ElasticCheck::where("alert_rule_id", $alert->_id)->where("state", ElasticCheck::FIRE)->first();
                if ($check && $check->state == ElasticCheck::FIRE) {
                    $check->state = ElasticCheck::RESOLVED;
                    $check->save();

                    ElasticHistory::create([
                        "alert_rule_id" => $alert->_id,
                        "alert_rule_name" => $alert->name,
                        "dataview_name" => $alert->dataview_name,
                        "dataview_title" => $alert->dataview_title,
                        "query_string" => $alert->query_string,
                        "conditionType" => $alert->conditionType,
                        "minutes" => $alert->minutes,
                        "count_document" => $alert->count_document,
                        "current_count_document" => -1,
                        "state" => ElasticCheck::RESOLVED,
                    ]);
                    $sendResolve = true;
                }
                break;
            case AlertRuleType::SPLUNK:
            case AlertRuleType::NOTIFICATION:
            case AlertRuleType::METABASE:
                break;
            case AlertRuleType::GRAFANA:
                break;

            /*     $alert = AlertRulePrometheus::firstWhere([
                     'name' => $id
                 ]);
                 if ($alert) {
                     SendNotifyJob::dispatch(SendNotifyJob::PROMETHEUS_TEST, $alert);
                 }
                 break;*/

        }
        if ($sendResolve) {
            SendNotifyService::CreateNotify(SendNotifyJob::RESOLVED_MANUALLY, $alert, $alert->_id);
        }
        return ['status' => true];
    }

    public function CreateAccessUser(Request $request, $service, $id)
    {
        $adminUserId = User::where('username', 'admin')->first()->_id;

        $chooseUsers = User::whereNotIn("_id", [$adminUserId, \Auth::id()])->get();

        $alert = AlertRule::where('_id', $id)->first();
        $data = [];
        if (!empty($alert->user_ids))
            $data = User::whereIn("_id", $alert->user_ids)->get();

        return view('content.pages.alerts.modal_create_access_user', compact('chooseUsers', "data"));
    }


    public function StoreAccessUser(Request $request, $service, $id)
    {


        if ($request->has("accessUsers") && !empty($request->post("accessUsers"))) {
//            $adminUserId = User::where('username', 'admin')->first()->_id;

            $alert = AlertRule::where('_id', $id)->first();
            foreach ($request->accessUsers as $userId) {
                $alert->push("user_ids", $userId, true);
            }
            $alert->save();

        }

        return ['success' => true];

    }

    public function DeleteAccessUser(Request $request, $service, $alertId, $userId)
    {

        $alert = AlertRule::where('_id', $alertId)->first();
        $alert->pull("user_ids", $userId);
        $alert->save();


        return ['success' => true];
    }

    public function CreateTag(Request $request, $id)
    {
        $alert = AlertRule::where('_id', $id)->first();
        $currentUser = Auth::user();
        if (!AlertRuleService::HasAdminAccessAlert($currentUser, $alert)) {
            abort(403);
        }
        $tags = $alert->tags ?? [];
        $tags = implode(",", $tags);
        return view('content.alerts.tags.modal_create_tags', compact("tags"));
    }

    public function StoreTag(Request $request, $id)
    {

        $alert = AlertRule::where('_id', $id)->first();
        $currentUser = Auth::user();
        $access = AlertRuleService::HasAdminAccessAlert($currentUser, $alert);

        if (!$access) {
            abort(403);
        }

        $alert->tags = TagService::GetTagsArray($request->tags);
        $alert->save();


        return ['success' => true];

    }

    public function Delete(Request $request)
    {

        $alert = AlertRule::where('_id', $request->id)->first();
        $userId = \Auth::user()->_id;
        if ($alert->user_id == $userId || \Auth::user()->hasRole("admin")) {
            $alertRuleId = $alert->_id;
            $type = $alert->type;
            $alertname = $alert->alertname;
            $alert->delete();
            switch ($type) {
                case AlertRuleType::API:
                case AlertRuleType::NOTIFICATION:
                    AlertInstance::where("alertname", $alertname)->delete();
                    break;
                case AlertRuleType::PROMETHEUS:
                    PrometheusCheck::where("alert_rule_id", $alertRuleId)->delete();
                    break;
                case AlertRuleType::HEALTH:
                    HealthCheck::where("alert_rule_id", $alertRuleId)->delete();
                    break;
                case AlertRuleType::ELASTIC:
                    ElasticCheck::where("alert_rule_id", $alertRuleId)->delete();
                    break;
            }

        } else {
            $alert->pull("user_ids", $userId);
            if (!empty($alert->endpoint_ids)) {
                $data = Endpoint::whereIn("_id", $alert->endpoint_ids)->where('user_id', \Auth::user()->_id)->get();
                foreach ($data as $endpoint) {
                    $alert->pull("endpoint_ids", $endpoint->_id);
                }

            }

        }

        return ['success' => true];
//        return redirect()->route('role.index');
    }


}
