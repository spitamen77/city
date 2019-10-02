<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 17.07.2019 12:20
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    protected $table = 'my_business_tags';

    public $timestamps = false;
    protected $primaryKey = 'tag_id';
    protected $fillable = [
        'name',
        'frequency',
    ];

    protected $casts = [
        'name' =>'string',
        'frequency' =>'int',

    ];
}
