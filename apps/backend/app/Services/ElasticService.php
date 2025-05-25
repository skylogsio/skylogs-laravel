<?php

namespace App\Services;

use App\Models\AlertInstance;
use App\Models\AlertRule;
use App\Models\AlertRuleGrafana;
use App\Models\ElasticCheck;
use App\Models\Endpoint;
use App\Models\GrafanaInstance;
use App\Models\SentryWebhookAlert;
use App\Helpers\Call;
use App\Helpers\Constants;
use App\Helpers\SMS;
use App\Helpers\Telegram;
use Carbon\Carbon;

class ElasticService
{
    public static function getDocuments(ElasticCheck $elasticCheck): array
    {
        $documents = [];

        $dataSource = $elasticCheck->alertRule->dataSource;
        try {
            $nowCarbon  = Carbon::now("UTC");
            $nowString = $nowCarbon->format("Y-m-d\TH:i:s");
            $agoString = $nowCarbon->subMinutes($elasticCheck->minutes)->format("Y-m-d\TH:i:s");
//        dd($nowString,$agoString);
            $response = \Http::acceptJson()
                ->withBasicAuth($dataSource->username, $dataSource->password)
                ->post($dataSource->url."/$elasticCheck->dataviewTitle/_search",
                [
                    "size" => $elasticCheck->countDocument + 10,
                    "query" => [
                        "query_string" => [
                            "query" => "timestamp:[$agoString TO $nowString] $elasticCheck->queryString",
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
