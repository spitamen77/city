<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 12.04.2019 16:25
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryTranslation extends Model
{
    protected $table = 'my_category_translation';

    public $timestamps = false;

    protected $fillable = [
        'translation_id',
        'lang_code',
        'category_type',
        'title',
        'slug',
    ];

    protected $casts = [
        'translation_id' => 'int',
        'lang_code'=>'string',
        'category_type'=>'string',
        'title'=>'string',
        'slug'=>'string',
    ];
}
