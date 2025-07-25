<?php

namespace App\Helpers;

use App\interfaces\Messageable;

use App\Models\Endpoint;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class MatterMost
{

    public static function createText($message): array
    {
        $formatted_Card_Payload = [
            "text" => $message,
        ];
        return $formatted_Card_Payload;
    }


    public static function sendMessageAlert($urls, Messageable $alert): array
    {
        $responses = [];
        if (empty($urls)) return $responses;

        $result = Http::pool(function (Pool $pool) use ($urls, $alert, $responses) {
            foreach ($urls as $url) {

                $responses[] = $pool->acceptJson()->post($url, self::createText($alert->matterMostMessage()));
            }

            return $responses;
        });

        $resultJson = [];

        foreach ($result as $item) {

            try {

                if ($item instanceof Response) {
                    $resultJson[] = $item->json();
                } else
                    $resultJson[] = $item->getMessage();

            } catch (\Exception $e) {
                $resultJson[] = $e->getMessage();
            }
        }

        return $resultJson;

    }

}
