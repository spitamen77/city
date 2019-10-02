<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 10.07.2019 11:44
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class BusinessContacts extends Model
{
    protected $table = 'my_business_contacts';

    public $timestamps = false;

    protected $fillable = [
        'business_id',
        'phone',

    ];

    protected $casts = [
        'business_id' =>'int',
        'phone' =>'int',

    ];
}
