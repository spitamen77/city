<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 18.04.2019 15:39
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class BusinessTranslation extends Model
{
    protected $table = 'my_business_translation';
    public $timestamps = false;

    protected $fillable = [
        'business_id',
        'title',
        'description',
        'lang',
    ];

    protected $casts = [
        'business_id' =>'int',
        'title' =>'string',
        'description' =>'string',
        'lang' =>'string',

    ];
}
