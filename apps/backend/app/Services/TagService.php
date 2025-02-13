<?php

namespace App\Services;

use App\Models\AlertRule;
use App\Models\ApiAlertHistory;
use App\Utility\Constants;
use Carbon\Carbon;

class TagService
{

    public static function GetTagsArray($input): array
    {
        $postTags = is_array($input) ? $input : json_decode($input, );
        $newTags = collect($postTags);
        $cleaned = $newTags->map(fn($item) => trim($item->value))
            ->filter(fn($item) => !empty($item))
            ->unique()
            ->values();
        return $cleaned->toArray();

    }

}
