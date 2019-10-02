<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 26.04.2019 18:04
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    protected $table = 'my_messages';

    public $timestamps = false;
    protected $primaryKey = 'message_id';

    protected $fillable = [
        'from_id',
        'to_id',
        'from_uname',
        'to_uname',
        'message_content',
        'message_date',
        'recd',
        'seen',
        'message_type',
    ];

    protected $casts = [
        'from_id' =>'int',
        'to_id' =>'int',
        'from_uname' =>'string',
        'to_uname' =>'string',
        'message_content' =>'string',
        'recd' =>'int',
        'seen' =>'int',
        'message_type' =>'string',
    ];
}
