<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 18.04.2019 14:18
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Business extends Model
{
protected $table = 'my_business';

protected $fillable = [
    'user_id',
    'logotype',
    'address',
    'phone',
    'email',
    'website',
    'title',
    'description',
    'long_title',
    'views',
    'longitude',
    'latitude',
    'region_id',
    'city_id',
    'landmark',
    'admin_seen',
    'biz_type',
];

protected $casts = [
    'user_id' =>'int',
    'logotype' =>'string',
    'address' =>'string',
    'phone' =>'string',
    'email' =>'string',
    'website' => 'string',
    'title' => 'string',
    'description' => 'string',
    'long_title' => 'string',
    'longitude' => 'float',
    'latitude' => 'float',
    'views' => 'int',
    'city_id' => 'string',
    'region_id' => 'string',
    'admin_seen' => 'int',
    'landmark' => 'string',
    'biz_type' => 'int',
];
    public function review()
    {
        return $this->belongsTo('Reviews', 'reviewID')->where('type',Config::get('constants.business'));
    }

    public function images()
    {
        return $this->hasMany(BusinessImage::class, 'id', 'image_id');
    }

}
