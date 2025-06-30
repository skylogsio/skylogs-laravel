<?php

namespace App\Models\Profile;

use App\Interfaces\Messageable;
use App\Models\AlertRule;
use App\Models\BaseModel;
use App\Models\User;
use App\Observers\EndpointObserver;
use App\Services\EndpointService;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class ProfileService extends BaseModel
{

    public $timestamps = true;

    protected $guarded = ['id','_id',];

}
