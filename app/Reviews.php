<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 13.04.2019 12:11
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reviews extends Model
{
    protected $table = 'my_reviews';
    public $timestamps = false;

    protected $fillable = [
        'reviewID',
        'productID',
        'user_id',
        'rating',
        'comments',
        'date',
        'publish',
        'type',
    ];

    protected $casts = [
        'reviewID' => 'int',
        'productID'=>'int',
        'user_id'=>'int',
        'rating'=>'int',
        'comments'=>'string',
        'publish'=>'int',
        'type'=>'int',
        'date'=>'int'
    ];

    public static function sum($item_id,$type)
    {
        return Reviews::where('productID',$item_id)
            ->where('type',$type)
            ->sum('rating');
    }

    public static function count($item_id,$type)
    {
        return Reviews::where('productID',$item_id)
            ->where('type',$type)
            ->count();
    }
}
