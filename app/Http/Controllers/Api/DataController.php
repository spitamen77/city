<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 12.04.2019 19:15
 */

namespace App\Http\Controllers\Api;


use App\Business;
use App\BusinessCategory;
use App\BusinessImage;
use App\CategoryMain;
use App\CategorySub;
use App\CategoryTranslation;
use App\Cities;
use App\CitiesTranslation;
use App\CityCenter;
use App\Exchange;
use App\Notification;
use App\State;
use App\User;
use App\Weather;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class DataController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['index','data','viloyat','exchange','weather','kurs','obhavo']);
    }

    public function data(Request $request)
    {
        if ($request->lang=='en'){
            $category = CategoryMain::all()->toArray();
            $res=[];
            foreach ($category as $item){
                unset($item['cat_order'],$item['picture']); //$item['icon']
                $subcategory = CategorySub::where('main_cat_id',$item['cat_id'])
                    ->select('sub_cat_id','sub_cat_name','slug')
                    ->get()->toArray();
                $item['sub']=$subcategory;
                $res[]=$item;
            }
            return $res;
        }
        else {
            $category = CategoryTranslation::where('lang_code',$request->lang)
                ->where('category_type','main')
                ->get()->toArray();
            $res=[];
            foreach ($category as $item){
                $item['cat_id'] = $item['translation_id'];
                $item['cat_name'] = $item['title'];
                $cat = CategoryMain::where('cat_id',$item['translation_id'])->first();
                $subcategory = CategorySub::where('main_cat_id',$item['translation_id'])->get()->toArray();
                $subarray=[];
                foreach ($subcategory as $i){
                    $subarray[] = $i['sub_cat_id'];
                }
                $subres = [];
                $subcat_trans = CategoryTranslation::where('category_type','sub')
                    ->select('translation_id','title','slug')
                    ->where('lang_code',$request->lang)
                    ->whereIn('translation_id',$subarray)
                    ->get()->toArray();
                foreach ($subcat_trans as $sub){
                    $sub['sub_cat_id'] = $sub['translation_id'];
                    $sub['sub_cat_name'] = $sub['title'];
                    unset($sub['title'], $sub['translation_id']);
                    $subres[]=$sub;
                }
                unset($item['id'],$item['lang_code'],$item['category_type'],$item['title'],$item['translation_id']);
                $item['icon'] = $cat['icon'];
                $item['sub']=$subres;
                $res[]=$item;
//            var_dump($subcat_trans);exit;
            }
            return $res;
        }
    }

    public function viloyat(Request $request)  // Keyinchalik talabga o`zgartirish kitilgani bois, kengayib ketgan
    {
        if (isset($request->lang)){
            switch ($request->lang) {
                case "uz":
                    $lang = CitiesTranslation::where('category_type','main')->where('lang_code',$request->lang)->get();
                    $res = []; $cat=[];
                    foreach ($lang as $item){
                        switch ($item->state->code) {
                            case "UZ.15":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1513886)->first()->toArray();
                                break;
                            case "UZ.08":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1216311)->first()->toArray();
                                break;
                            case "UZ.10":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1216265)->first()->toArray();
                                break;
                            case "UZ.03":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1514019)->first()->toArray();
                                break;
                            case "UZ.02":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1217662)->first()->toArray();
                                break;
                            case "UZ.14":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1512524)->first()->toArray();
                                break;
                            case "UZ.16":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1513966)->first()->toArray();
                                break;
                            case "UZ.07":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1513131)->first()->toArray();
                                break;
                            case "UZ.06":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1513157)->first()->toArray();
                                break;
                            case "UZ.01":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1514588)->first()->toArray();
                                break;
                            default:
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->first()->toArray();
                        }
                        $subcategory = Cities::where('subadmin1_code', $item->state->code)
                            ->select('id', 'code', 'asciiname')
                            ->get()->toArray();
                        $subarray=[];
                        foreach ($subcategory as $i){
                            $subarray[] = $i['id'];
                        }
                        $sublang = CitiesTranslation::where('category_type','sub')
                            ->where('lang_code',$request->lang)
                            ->whereIn('translation_id',$subarray)
                            ->get();
                        $subtrans=[]; $array=[];
                        foreach ($sublang as $sub){
                            $subtrans['id'] = $sub->translation_id;
                            $subtrans['code'] = $sub->city->code;
                            $subtrans['asciiname'] = $sub->title;
                            array_push($array,$subtrans);
                        }

                        $cat['id'] = $item->translation_id;
                        $cat['code'] = $item->state->code;
                        $cat['name'] = $item->title;
                        $cat['longitude'] = $koor['latitude'];
                        $cat['latitude'] = $koor['longitude'];
                        $cat['sub'] = $array;
                        $res[] = $cat;
                        unset($array,$subtrans);
                    }
                    break;
                case "ru":
                    $lang = CitiesTranslation::where('category_type','main')->where('lang_code',$request->lang)->get();
                    $res = []; $cat=[];
                    foreach ($lang as $item){
                        switch ($item->state->code) {
                            case "UZ.15":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1513886)->first()->toArray();
                                break;
                            case "UZ.08":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1216311)->first()->toArray();
                                break;
                            case "UZ.10":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1216265)->first()->toArray();
                                break;
                            case "UZ.03":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1514019)->first()->toArray();
                                break;
                            case "UZ.02":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1217662)->first()->toArray();
                                break;
                            case "UZ.14":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1512524)->first()->toArray();
                                break;
                            case "UZ.16":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1513966)->first()->toArray();
                                break;
                            case "UZ.07":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1513131)->first()->toArray();
                                break;
                            case "UZ.06":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1513157)->first()->toArray();
                                break;
                            case "UZ.01":
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->where('id', 1514588)->first()->toArray();
                                break;
                            default:
                                $koor = CityCenter::where('subadmin1_code', $item->state->code)->first()->toArray();
                        }
                        $subcategory = Cities::where('subadmin1_code', $item->state->code)
                            ->select('id', 'code', 'asciiname')
                            ->get()->toArray();
                        $subarray=[];
                        foreach ($subcategory as $i){
                            $subarray[] = $i['id'];
                        }
                        $sublang = CitiesTranslation::where('category_type','sub')
                            ->where('lang_code',$request->lang)
                            ->whereIn('translation_id',$subarray)
                            ->get();
                        $subtrans=[]; $array=[];
                        foreach ($sublang as $sub){
                            $subtrans['id'] = $sub->translation_id;
                            $subtrans['code'] = $sub->city->code;
                            $subtrans['asciiname'] = $sub->title;
                            array_push($array,$subtrans);
                        }

                        $cat['id'] = $item->translation_id;
                        $cat['code'] = $item->state->code;
                        $cat['name'] = $item->title;
                        $cat['longitude'] = $koor['latitude'];
                        $cat['latitude'] = $koor['longitude'];
                        $cat['sub'] = $array;
                        $res[] = $cat;
                        unset($array,$subtrans);
                    }
                    break;
                default:
                    $category = State::where('active', 1)->select('id', 'code', 'name')->get()->toArray();
                    $res = [];
                    foreach ($category as $item) {
                        switch ($item['code']) {
                            case "UZ.15":
                                $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1513886)->first()->toArray();
                                break;
                            case "UZ.08":
                                $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1216311)->first()->toArray();
                                break;
                            case "UZ.10":
                                $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1216265)->first()->toArray();
                                break;
                            case "UZ.03":
                                $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1514019)->first()->toArray();
                                break;
                            case "UZ.02":
                                $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1217662)->first()->toArray();
                                break;
                            case "UZ.14":
                                $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1512524)->first()->toArray();
                                break;
                            case "UZ.16":
                                $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1513966)->first()->toArray();
                                break;
                            case "UZ.07":
                                $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1513131)->first()->toArray();
                                break;
                            case "UZ.06":
                                $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1513157)->first()->toArray();
                                break;
                            case "UZ.01":
                                $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1514588)->first()->toArray();
                                break;
                            default:
                                $koor = CityCenter::where('subadmin1_code', $item['code'])->first()->toArray();
                        }

                        $item['longitude'] = $koor['latitude'];
                        $item['latitude'] = $koor['longitude'];
                        $subcategory = Cities::where('subadmin1_code', $item['code'])
                            ->select('id', 'code', 'asciiname')
                            ->get()->toArray();
                        $item['sub'] = $subcategory;
                        $res[] = $item;
                    }
            }
        }
        else {
            $category = State::where('active', 1)->select('id', 'code', 'name')->get()->toArray();
            $res = [];
            foreach ($category as $item) {
                switch ($item['code']) {
                    case "UZ.15":
                        $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1513886)->first()->toArray();
                        break;
                    case "UZ.08":
                        $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1216311)->first()->toArray();
                        break;
                    case "UZ.10":
                        $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1216265)->first()->toArray();
                        break;
                    case "UZ.03":
                        $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1514019)->first()->toArray();
                        break;
                    case "UZ.02":
                        $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1217662)->first()->toArray();
                        break;
                    case "UZ.14":
                        $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1512524)->first()->toArray();
                        break;
                    case "UZ.16":
                        $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1513966)->first()->toArray();
                        break;
                    case "UZ.07":
                        $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1513131)->first()->toArray();
                        break;
                    case "UZ.06":
                        $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1513157)->first()->toArray();
                        break;
                    case "UZ.01":
                        $koor = CityCenter::where('subadmin1_code', $item['code'])->where('id', 1514588)->first()->toArray();
                        break;
                    default:
                        $koor = CityCenter::where('subadmin1_code', $item['code'])->first()->toArray();
                }

                $item['longitude'] = $koor['latitude'];
                $item['latitude'] = $koor['longitude'];
                $subcategory = Cities::where('subadmin1_code', $item['code'])
                    ->select('id', 'code', 'asciiname')
                    ->get()->toArray();
                $item['sub'] = $subcategory;
                $res[] = $item;
            }
        }
        return $res;
    }

    public function exchange() /* Cron uchun qilingan*/
    {
        $link = "https://nbu.uz/exchange-rates/json/";
        $result = file_get_contents($link);
        $obj = json_decode($result);
        $kurs = Exchange::where('status',1)->get();
        foreach ($obj as $item){
            if ($item->code=="EUR" || $item->code=="USD" || $item->code=="RUB" || $item->code=="CNY"){
                $item->date = strtotime($item->date);
                if (isset($kurs)){
                    foreach ($kurs as $lar){
                        $lar->delete();
                    }
                    $withdraw = Exchange::create([
                        'status' => 1,
                        'title' => $item->title,
                        'code' => $item->code,
                        'cb_price' => $item->cb_price,
                        'date' => $item->date,
                    ]);

                }
                else {
                    $withdraw = Exchange::create([
                        'status' => 1,
                        'title' => $item->title,
                        'code' => $item->code,
                        'cb_price' => $item->cb_price,
                        'date' => $item->date,
                    ]);
                }
            }
        }
    }

    public function kurs()  /* Api uchun*/
    {
        $kurs = Exchange::where('status',1)->get();
        return response()->json(
            $kurs
        );

    }

    public function weather() /* Cron uchun */
    {
        $city = ['andijan','bukhara','guliston','jizzakh','qarshi','navoiy','namangan','nukus','samarkand','tashkent','termiz','urgench','fergana'];
        foreach ($city as $key => $value) {
            $url = 'http://api.apixu.com/v1/forecast.json?key=7328cb9599554e63815115438191704&q='.$value.'&days=6';
            $str=file_get_contents($url);
            $data[] = json_decode($str);
        }
        $del = Weather::where('status',1)->get();
        foreach ($del as $key => $value) {
            $value->delete();
        }
        foreach ($data as $wr) {
            foreach ($wr->forecast->forecastday as $weth){
                if (!empty($wr)) {
                    $withdraw = Weather::create([
                        'city' => $wr->location->name,
                        'date' => $wr->current->last_updated_epoch,
                        'air_temp' => (float)$wr->current->temp_c,
                        'title' => (string)$wr->location->country,
                        'name' => (string)$wr->location->region,
                        'datetime' => (string)$weth->date,
                        'cloud_amount' => (string)$weth->day->condition->text,
                        'weather_code' => (string)$weth->day->condition->code,
                        'icon' => (string)$weth->day->condition->icon,
                        'max_temp' => (string)$weth->day->maxtemp_c,
                        'min_temp' => (string)$weth->day->mintemp_c,
                        'status' => 1,
                    ]);

                }

            }
        }
    }

    public function obhavo()  /* Api uchun*/
    {
        $weather = Weather::where('status',1)->get();
        return response()->json([
            $weather
        ]);

    }

    public function notification()
    {
        $notic = Notification::where('user_id',Auth::user()->id)->get()->toArray();
        $count = count($notic);
        $business=[]; $service=[];
        foreach ($notic as $item){
            if ($item['category_type']==Config::get('constants.product')){
                $service[]=$item;
            }
            else $business[]=$item;
        }
        return response()->json([
            'count' => $count,
            'business' => $business,
            'service' =>$service
        ]);
    }

}
