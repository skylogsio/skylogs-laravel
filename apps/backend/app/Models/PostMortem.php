<?php

namespace App\Models;

use App\interfaces\Messageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class PostMortem extends Model
{

    public $timestamps = true;
    public static $title = "Post Mortem";
    public static $KEY = "post-portem";

    protected $guarded = ['id','_id',];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


}
