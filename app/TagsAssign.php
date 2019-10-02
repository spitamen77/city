<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 17.07.2019 12:22
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class TagsAssign extends Model
{
    protected $table = 'my_business_tags_assign';

    public $timestamps = false;
    protected $fillable = [
        'business_id',
        'tag_id',
        'type',
    ];

    protected $casts = [
        'business_id' =>'int',
        'tag_id' =>'int',
        'type' =>'int',
    ];

    public function tag(){
        return $this->hasOne(Tags::class, 'tag_id', 'tag_id');
    }

    public static function tags($business_id,$type){
        $tags = self::where('business_id',$business_id)->where('type',$type)->get();
        $res =[]; $natija=[];
        foreach ($tags as $item){
            $res['tag_id']=$item->tag->tag_id;
            $res['name']=$item->tag->name;
            $natija[]=$res;
        }
        return $natija;
    }
}
