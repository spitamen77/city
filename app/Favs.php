<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 25.07.2019 14:51
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Favs extends Model
{
    protected $table = 'my_favs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'business_id',
    ];

    protected $casts = [
        'user_id'=>'int',
        'business_id'=>'int',
    ];

}
