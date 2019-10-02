<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 25.05.2019 11:32
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class BusinessImage extends Model
{
    protected $table = 'my_business_image';

    public $timestamps = false;

    protected $fillable = [
        'image_id',
        'image',
        'type',
    ];

    protected $casts = [
        'image_id' => 'int',
        'image' => 'string',
        'type' => 'int',
    ];
}