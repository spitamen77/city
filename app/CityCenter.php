<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 26.06.2019 11:02
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class CityCenter extends Model
{
    protected $table = 'my_cities';

    protected $fillable = [
        'country_code',
        'name',
        'asciiname',
        'longitude',
        'latitude',
        'subadmin1_code',
    ];

    protected $casts = [
        'country_code' => 'string',
        'name'=>'string',
        'asciiname'=>'string',
        'longitude'=>'float',
        'latitude'=>'float',
        'subadmin1_code'=>'string',
    ];

}
