<?php

namespace App\Helpers;

use App\interfaces\Messageable;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Call
{

    private static function Url()
    {

        return "https://api.kavenegar.com/v1". '/' . self::Token() . "/call/maketts.json";
    }

    private static function SenderNumber()
    {

        return "10007891";
    }

    private static function Token()
    {

        return config("variables.kavenegarToken");
    }



    public static function sendAlert($nums,Messageable $alert)
    {

        if (empty($nums)) return "";

        $result = Http::pool(function (Pool $pool) use ($nums, $alert) {
            if ($nums instanceof Collection)
                $numsString = $nums->implode(",");
            else
                $numsString = implode(",", $nums);

            return $pool->get(self::Url(), [
                'sender' => self::SenderNumber(),
                'message' => $alert->callMessage(),
                "receptor" => $numsString,
            ]);
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
