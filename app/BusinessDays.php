<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 23.05.2019 12:19
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class BusinessDays extends Model
{
    protected $table = 'my_business_days';

    public $timestamps = false;

    protected $fillable = [
        'business_id',
        'open_time',
        'from_uname',
        'close_time',
        'day',
        'status',
    ];

    protected $casts = [
        'business_id' => 'int',
        'open_time' => 'int',
        'from_uname' => 'int',
        'close_time' => 'int',
        'day' => 'int',
        'status' => 'int',
    ];
}