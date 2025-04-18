<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Morilog\Jalali\Jalalian;

class Site {
    public static $types = [

    ];

    public static $labels = [

        "created_at"    => "تاریخ ایجاد",
        "updated_at"    => "تاریخ بروزرسانی",
        "deleted_at"    => "تاریخ حذف",
        "created_by"    => "ایجاد توسط",
        "updated_by"    => "بروزرسانی توسط",
        "deleted_by"    => "حذف توسط",



    ];
    public static $field_type = [



    ];


}
