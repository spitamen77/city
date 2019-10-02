<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 13.04.2019 11:07
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $table = 'my_subadmin1';

    protected $fillable = [
        'country_code',
        'name',
        'asciiname',
        'code',
        'active',
    ];

    protected $casts = [
        'country_code' => 'string',
        'name'=>'string',
        'asciiname'=>'string',
        'code'=>'string',
        'active'=>'int',
    ];
}
