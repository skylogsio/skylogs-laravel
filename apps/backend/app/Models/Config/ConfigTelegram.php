<?php

namespace App\Models\Config;

use App\Interfaces\Messageable;
use App\Models\AlertRule;
use App\Models\BaseModel;
use App\Models\User;
use App\Observers\ConfigTelegramObserver;
use App\Observers\EndpointObserver;
use App\Services\EndpointService;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

#[ObservedBy(ConfigTelegramObserver::class)]
class   ConfigTelegram extends BaseModel
{

    public $timestamps = true;

    protected $guarded = ['id', '_id',];




}
