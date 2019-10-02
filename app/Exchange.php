<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 17.04.2019 13:01
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Exchange extends Model
{
    protected $table = 'my_exchange';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'code',
        'cb_price',
        'date',
        'status',
    ];

    protected $casts = [
        'cb_price'=>'float',
        'title'=>'string',
        'code'=>'string',
        'date'=>'int',
    ];

}



