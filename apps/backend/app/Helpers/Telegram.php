<?php

namespace App\Helpers;

use App\interfaces\Messageable;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Telegram
{
    private static function Url(): string
    {
        return config("variables.telegramUrl","")."/send_message";
    }

    private static function token(): string
    {
        return config("variables.telegramBotToken","");
    }



    public static function sendMessageAlert($chatIds, Messageable $alert): array
    {
        $responses = [];
        if (empty($chatIds)) return $responses;

        $result = Http::pool(function (Pool $pool) use ($chatIds, $alert) {
            foreach ($chatIds as $chat)
                $pool->acceptJson()->post(self::Url(), [
                    'apiToken' => self::token(),
                    'chatID' => $chat['chatId'],
                    'message' => $alert->telegramMessage(),
                    "thread_id" => $chat['threadId']
                ]);

            return [];
        });

        $resultJson = [];
        foreach ($result as $item) {

            try {
                if($item instanceof Response) {
                    $resultJson[] = $item->json();
                }else
                    $resultJson[] = $item->getMessage();
            } catch (\Exception $e) {
                $resultJson[] = $e->getMessage();
            }

        }

        return $resultJson;

    }


}
