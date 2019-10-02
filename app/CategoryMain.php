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

class CategoryMain extends Model
{
    protected $table = 'my_catagory_main';

    public $timestamps = false;

    protected $fillable = [
        'cat_id',
        'cat_order',
        'cat_name',
        'slug',
        'icon',
        'picture',
    ];

    protected $casts = [
        'cat_order' => 'int',
        'cat_name'=>'string',
        'slug'=>'string',
        'icon'=>'string',
        'picture'=>'string',
    ];

}
