<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 12.04.2019 14:58
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'my_notification';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'message',
        'status',
        'date',
        'category_type',
        'category_id',
    ];

    protected $hidden = [
        'updated_at', 'created_at',
    ];

    protected $casts = [
        'user_id' => 'int',
        'message'=>'string',
        'status'=>'int',
        'date'=>'int',
        'category_type'=>'int',
        'category_id'=>'int',
    ];

}
