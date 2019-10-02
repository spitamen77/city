<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 12.04.2019 16:24
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategorySub extends Model
{
    protected $table = 'my_catagory_sub';

    public $timestamps = false;

    protected $fillable = [
        'sub_cat_id',
        'main_cat_id',
        'sub_cat_name',
        'slug',
        'cat_order',
        'photo_show',
        'price_show',
    ];

    protected $casts = [
        'main_cat_id' => 'int',
        'sub_cat_name'=>'string',
        'slug'=>'string',
        'cat_order'=>'string',
        'photo_show'=>'int',
        'price_show'=>'int',
    ];

}
