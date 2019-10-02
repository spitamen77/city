<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 13.04.2019 11:50
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'my_product';

    protected $fillable = [
        'user_id',
        'bussiness_id',
        'product_name',
        'slug',
        'description',
        'long_title',
        'category',
        'sub_category',
        'price',
        'phone',
        'view',
        'city',
        'state',
        'latlong',
        'screen_shot',
        'admin_seen',
        'created_at',
        'type',
    ];

    protected $casts = [
        'user_id' => 'int',
        'bussiness_id' => 'int',
        'product_name' => 'string',
        'slug' => 'string',
        'description' => 'string',
        'long_title' => 'string',
        'category' => 'int',
        'sub_category' => 'int',
        'price' => 'int',
        'phone' => 'int',
        'view' => 'int',
        'city' => 'int',
        'state' => 'string',
        'latlong' => 'string',
        'screen_shot' => 'string',
        'admin_seen' => 'int',
        'created_at' => 'string',
        'type' => 'int',
    ];
}
