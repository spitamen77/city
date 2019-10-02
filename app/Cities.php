<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 13.04.2019 11:02
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cities extends Model
{
    protected $table = 'my_subadmin2';

    protected $fillable = [
        'country_code',
        'name',
        'asciiname',
        'code',
        'subadmin1_code',
        'active',
    ];

    protected $casts = [
        'country_code' => 'string',
        'name'=>'string',
        'asciiname'=>'string',
        'code'=>'string',
        'subadmin1_code'=>'string',
        'active'=>'int',
    ];

}
