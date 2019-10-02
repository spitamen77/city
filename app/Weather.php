<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 17.04.2019 17:03
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Weather extends Model
{
    protected $table = 'my_weather';

    public $timestamps = false;

    protected $fillable = [
        'city',
        'date',
        'air_temp',
        'title',
        'name',
        'datetime',
        'cloud_amount',
        'weather_code',
        'icon',
        'max_temp',
        'min_temp',
        'status',
    ];

    protected $casts = [
        'city' =>'string',
        'date' =>'int',
        'air_temp' =>'float',
        'title' =>'string',
        'name' =>'string',
        'datetime' => 'string',
        'cloud_amount' =>'string',
        'weather_code' =>'int',
        'icon' =>'string',
        'max_temp' =>'float',
        'min_temp' =>'float',
        'status' =>'int'
    ];
}
