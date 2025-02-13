<?php

namespace App\Services;

use App\Models\AlertInstance;
use App\Models\AlertRule;
use App\Models\AlertRuleGrafana;
use App\Models\ElasticCheck;
use App\Models\Endpoint;
use App\Models\GrafanaInstance;
use App\Models\SentryWebhookAlert;
use App\Utility\Call;
use App\Utility\Constants;
use App\Utility\SMS;
use App\Utility\Telegram;
use Carbon\Carbon;

class ElasticService
{
    public static function getDocuments(ElasticCheck $elasticCheck): array
    {
        $documents = [];


        try {
            $nowCarbon  = Carbon::now("UTC");
            $nowString = $nowCarbon->format("Y-m-d\TH:i:s");
            $agoString = $nowCarbon->subMinutes($elasticCheck->minutes)->format("Y-m-d\TH:i:s");
//        dd($nowString,$agoString);
            $response = \Http::acceptJson()
                ->withBasicAuth(config("variables.elasticUser"), config("variables.elasticPass"))
                ->post(config("variables.elasticHost")."/$elasticCheck->dataview_title/_search",
                [
                    "size" => $elasticCheck->count_document + 10,
                    "query" => [
                        "query_string" => [
                            "query" => "timestamp:[$agoString TO $nowString] $elasticCheck->query_string",
                            "default_operator" => "AND"
                        ]
                    ]
                ]
            );
            $body = $response->json();

            $documents = $body['hits']["hits"];

        } catch (\Exception $exception) {

        }

        return $documents;

    }

}
