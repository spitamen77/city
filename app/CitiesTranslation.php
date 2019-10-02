<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 10.08.2019 9:44
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class CitiesTranslation extends Model
{
    protected $table = 'my_cities_translation';

    public $timestamps = false;

    protected $fillable = [
        'translation_id',
        'lang_code',
        'category_type',
        'title',
    ];

    protected $casts = [
        'translation_id' => 'int',
        'lang_code'=>'string',
        'category_type'=>'string',
        'title'=>'string',
    ];

    public function state(){
        return $this->hasOne(State::class, 'id', 'translation_id');
    }

    public function city(){
        return $this->hasOne(Cities::class, 'id','translation_id');
    }
}
