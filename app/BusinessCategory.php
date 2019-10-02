<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 18.04.2019 14:32
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class BusinessCategory extends Model
{
    protected $table = 'my_bussiness_categories';

    public $timestamps = false;
    protected $primaryKey = 'bussiness_id';
    protected $fillable = [
        'bussiness_id',
        'category_id',
        'sub_id',

    ];

    protected $casts = [
        'bussiness_id' =>'int',
        'category_id' =>'int',
        'sub_id' =>'int',

    ];

    public static function getSub($biz_id,$cat_id)
    {
        return self::where('category_id',$cat_id)->where('bussiness_id',$biz_id)->get();
    }
}
