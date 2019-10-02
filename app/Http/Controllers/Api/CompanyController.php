<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 13.04.2019 16:31
 */

namespace App\Http\Controllers\Api;

use App\Business;
use App\BusinessCategory;
use App\BusinessContacts;
use App\BusinessDays;
use App\BusinessImage;
use App\Cities;
use App\Favs;
use App\Http\Controllers\Controller;
use App\Notification;
use App\Product;
use App\Reviews;
use App\State;
use App\Tags;
use App\TagsAssign;
use http\Env\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use phpseclib\Net\SFTP;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['companySearch','businessAll','companyView','categoryData','getTags']);
    }

    public function businessAll(Request $request)
    {
        $limit = $request->perpage;
        $offset = $request->page-1;
        $business = Business::select('id','logotype','address','phone','title','description','created_at','admin_seen')
        ->where('admin_seen',Config::get('constants.admin_seen'));
        if ($request->perpage && $request->page) $posts = $business->skip($offset*$limit)->take($limit);
        $posts = $business->orderBy('id', 'desc')->get()->toArray();
        $res=[];
        foreach ($posts as $item){
//            unset($item['text'],$item['created_at'],$item['updated_at'],$item['longitude'],
//                $item['latitude'],$item['views'],$item['region_id'],$item['city_id'],
//                $item['admin_seen'],$item['user_id'],$item['email']);
            $category = BusinessCategory::where('bussiness_id',$item['id'])->get()->toArray();
            $kalit=[];
            foreach ($category as $key) /* Unikal qilib olinyapti */
            {
                if (in_array($key['category_id'], $kalit)) continue;
                else $kalit[]=$key['category_id'];
            }
            $days = BusinessDays::where('business_id',$item['id'])
                ->select('open_time','close_time','day')->get();
            $sum = Reviews::sum($item['id'],Config::get('constants.business'));
            $count = Reviews::count($item['id'],Config::get('constants.business'));
            if ($count==0) $soni = 0;
            else $soni = ceil($sum/$count);
            $item['category']=$kalit;
            $item['days']=$days;
            $item['rating']= $soni;
            $item['rating_count'] = $count;
            $res[]=$item;
        }
        return response()->json([
            'code' => 0,
            'data' => $res
        ]);

    }

    public function companySearch(Request $request)  // Bilmasangiz, bilgan kuyingizni chalingiz... degan metod ;)
    {
        $business = Business::where('admin_seen',Config::get('constants.admin_seen'));
        if (isset($request->category)){
            $business->join('my_bussiness_categories', 'my_business.id', '=', 'my_bussiness_categories.bussiness_id')
                ->where('category_id',$request->category);
        }
        if (isset($request->region_id)){
            if (isset($request->city_id)) {
                $business->where('city_id','=',$request->city_id);
            }
            else $business->where('region_id','=',$request->region_id);
        }
        if (isset($request->search)){
            $tag = Tags::where('name', 'LIKE', $request->search . '%')->first();
            if ($tag){
                $tags = TagsAssign::where('tag_id',$tag->tag_id)->get();
                $tras=[];
                foreach ($tags as $item){
                    $tras[]=$item->business_id;
                }
                if (is_array($tras)) {
                    $business->whereIn('my_business.id', $tras);

                    $business->where(function ($query) use ($request) {
                        $query->orWhere('my_business.address', 'LIKE', "%{$request->search}%")
                            ->orWhere('my_business.title','LIKE', "%{$request->search}%")
                            ->orWhere('my_business.description','LIKE', "%{$request->search}%")
                            ->orWhere('my_business.long_title','LIKE', "%{$request->search}%");
                    });
                }
            }
            else {
                $business->where(function ($query) use ($request) {
                    $query->orWhere('address', 'LIKE', "%{$request->search}%")
                        ->orWhere('title','LIKE', "%{$request->search}%")
                        ->orWhere('description','LIKE', "%{$request->search}%")
                        ->orWhere('long_title','LIKE', "%{$request->search}%");
                });
            }
        }
        if (isset($request->sort)){
            if ($request->sort=="rating"){
                $business->leftJoin('my_reviews', 'my_business.id', '=', 'my_reviews.productID')
                    ->where('type',Config::get('constants.business'))
                    ->orderBy('rating', 'desc');
            }
            else {
                $business->orderBy('views', 'desc');
            }
            $posts = $business->get()->toArray();  // ->orderBy('id', 'desc')
        }
        else $posts = $business->orderBy('id', 'desc')->get()->toArray();
//                    dd($business->toSql());
        if ($request->radius) {
//            $lat = 41.2921396;
//            $lng = 69.2311455;

            $radius = Queque::getRadius($request->lat, $request->lng, $request->radius);
            $compradius = [];$kalit=[]; $cat = [];
            foreach ($posts as $item) {
                if (in_array($item['id'], $kalit)) continue;
                else $kalit[]=$item['id'];
                $category = BusinessCategory::where('bussiness_id',$item['id'])->get()->toArray();
                foreach ($category as $key) /* Unikal qilib olinyapti */
                {
                    if (in_array($key['category_id'], $cat)) continue;
                    else $cat[]=$key['category_id'];
                }
                $sum = Reviews::sum($item['id'],Config::get('constants.business'));
                $count = Reviews::count($item['id'],Config::get('constants.business'));
                if ($count==0) $soni = 0;
                else $soni = ceil($sum/$count);
                if (isset($request->ball)){
                    if ($soni<$request->ball) continue;
                }
                if ($item['latitude'] > $radius['minLat'] && $item['latitude'] < $radius['maxLat']) {
                    if ($item['longitude'] > $radius['minLng'] && $item['longitude'] < $radius['maxLng']) {
                        $rad = Queque::distance($item['latitude'],$item['longitude'],$request->lat,$request->lng,"K");
                        $days = BusinessDays::where('business_id',$item['id'])
                            ->select('open_time','close_time','day')->get();
                        $item['masofa']=round($rad, 1);
                        $item['days']= $days;
                        $item['rating']= $soni;
                        $item['rating_count'] = $count;
                        $item['category'] = $cat;
                        $compradius[] = $item;
                    }
                }
            }
            return response()->json([
                'code' => 0,
                'data' => $compradius
            ]);
        }
        $compa = [];$kalit=[]; $cat = [];
        foreach ($posts as $item){
            if (in_array($item['id'], $kalit)) continue;
            else $kalit[]=$item['id'];
            $category = BusinessCategory::where('bussiness_id',$item['id'])->get()->toArray();
            foreach ($category as $key) /* Unikal qilib olinyapti */
            {
                if (in_array($key['category_id'], $cat)) continue;
                else $cat[]=$key['category_id'];
            }
            $sum = Reviews::sum($item['id'],Config::get('constants.business'));
            $count = Reviews::count($item['id'],Config::get('constants.business'));
            if ($count==0) $soni = 0;
            else $soni = ceil($sum/$count);
            if (isset($request->ball)){
                if ($soni<$request->ball) continue;
            }
            $days = BusinessDays::where('business_id',$item['id'])
                ->select('open_time','close_time','day')->get();
            $item['masofa']=0;
            $item['days']= $days;
            $item['ratings']= $soni;
            $item['rating_count'] = $count;
            $item['category'] = $cat;
            $compa[] = $item;
        }

        return response()->json([
            'code' => 0,
            'data' => $compa
        ]);
    }

    public function companyAdd(Request $request)
    {
        $this->validate($request,[
            'address' => 'required',
            'phone' => 'required|string',
//            'email' => 'required|string',
//            'website' => 'required',
            'category' => 'required',
            'landmark' => 'required',
            'text' => 'required',
            'title'=>'required',
            'description' => 'required',
            'logotype' => 'mimes:png,jpg,jpeg,svg,gif',
//            'image' => 'mimes:png,jpg,jpeg,svg,gif'
        ]);
        $string = '$sana='.$request->days.';';
        eval($string);  //havfli funksiya ekan

        $region = State::where('id',$request->region_id)->first();
        $city = Cities::where('id',$request->city_id)->first();
        $pieces = explode(",", $request->phone);
        $withdraw = Business::create([
            'user_id' => Auth::user()->id,
            'address' => $request->address,
            'phone' => $pieces[0],
            'email' => $request->email,
            'website' => $request->website,
            'title' => $request->title,
            'description' => $request->description,
            'long_title' => $request->text,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'landmark' => $request->landmark,
            'views' => 0,
            'region_id' =>$request->region_id,
            'city_id' =>$request->city_id,
            'biz_type' =>$request->biz_type,
        ]);
        foreach ($pieces as $key => $piece2){
            if ($key==0)continue;
            if (isset($piece2)){
                $bizcon = BusinessContacts::create([
                    'business_id' => $withdraw->id,
                    'phone' => $piece2,
                ]);
            }
        }
        if($request->tags!=null){
            $tags = explode(",", $request->tags);
            foreach ($tags as $key => $tag){
                $tag2 = trim($tag);
                $tag_id = Tags::where('name',$tag2)->first();
                if ($tag_id){
                    $assign = TagsAssign::create([
                        'business_id' => $withdraw->id,
                        'tag_id' => $tag_id->tag_id
                    ]);
                }
                else {
                    $biztag = Tags::create([
                        'name' =>$tag2,
                    ]);
                    $assign = TagsAssign::create([
                        'business_id' => $withdraw->id,
                        'tag_id' => $biztag->tag_id
                    ]);
                }
            }
        }
        if ($request->hasFile('logotype')) {
            $business = Business::where('id',$withdraw->id)->first();
            $image = $request->file('logotype');
            $filename = Auth::user()->username."_". time() . '.' . $image->getClientOriginalExtension();
            $location = '../storage/app/public/'. $filename;
            Image::make($image)->save($location);
            $business->logotype = "/storage/business/".$filename;
            $business->save();

            $upload = Queque::upload();
            $upload->chdir('business'); // open directory 'test'
            $link = 'https://api.my-city.uz/storage/'.$filename;
            $upload->put($filename, $link,SFTP::SOURCE_LOCAL_FILE);
            $upload->_disconnect(true);
            @unlink($location);
        }

        $str = '$cat='.$request->category.';';
        eval($str);
        foreach ($cat as $item){
            foreach ($item['sub'] as $sub){
                $bc = BusinessCategory::create([
                    'bussiness_id'=>$withdraw->id,
                    'category_id'=>$item['cat'],
                    'sub_id'=>$sub,
                ]);
            }
        }

        foreach ($sana as $key => $day)
        {
            $buc = BusinessDays::create([
                'business_id'=>$withdraw->id,
                'open_time'=>$day['start'],
                'close_time'=>$day['end'],
                'day'=>$day['day'],
                'status'=>1,
            ]);
        }

        return response()->json([
            'code' => 0,
            'business_id' => $withdraw->id,
            'message' => 'Успешно добавлено'
        ]);
    }

    public function companyView($id)
    {
        $business = Business::where('id',$id)->first();
        $business->views = $business->views+1;
        $business->save();
        $date=$business->created_at;
        $business->create_date=strtotime($date);
        $business->text = $business->long_title;
        $phones = BusinessContacts::where('business_id',$id)->get();
        foreach ($phones as $item){
            $business->phone.=",".$item->phone;
        }
        unset($business->updated_at,$business->created_at,$business->long_title);
        $rating = Reviews::where('productID',$id)
            ->join('my_user', 'my_reviews.user_id', '=', 'my_user.id')
            ->select('name','rating','comments','date','image')
            ->where('type',Config::get('constants.business'))
            ->get()->toArray();

        $category = BusinessCategory::where('bussiness_id',$id)->get()->toArray();
        $kalit=[]; $cat = [];  $main_cat=[]; $kat=[];
        foreach ($category as $key) /* Unikal qilib olinyapti */
        {
            if (in_array($key['category_id'], $kat)) continue;
            else {
            $sub = BusinessCategory::getSub($id,$key['category_id']);
            $kat[]=$key['category_id'];
                $sub2 = [];
                $cat['cat']=$key['category_id'];
                foreach ($sub as $sub_id){
                    $sub2[] = $sub_id['sub_id'];
                }
                $cat['sub']=$sub2;
                $kalit[] = $cat;
                unset($cat,$sub2);
            }
        }
        $days = BusinessDays::where('business_id',$id)
            ->select('open_time','close_time','day')->get();

        $sum = Reviews::sum($id,Config::get('constants.business'));
        $count = Reviews::count($id,Config::get('constants.business'));
        $images = BusinessImage::where('image_id',$id)
                ->where('type',Config::get('constants.business'))->get();
        $firma = []; $firma2=[];
        $product = Product::where('bussiness_id',$id)->get();
        foreach ($product as $prod){
            $sum2 = Reviews::sum($prod->id,Config::get('constants.product'));
            $count2 = Reviews::count($prod->id,Config::get('constants.product'));
            if ($count2==0) $soni2 = 0;
            else $soni2 = ceil($sum2/$count2);
            $firma['rating']=$soni2;
            $firma['rating_count']=$count2;
            $firma['produkt']=$prod;
            $firma2[]=$firma;
        }
        if ($count==0) $soni = 0;
        else $soni = ceil($sum/$count);
        $tag = TagsAssign::tags($business->id,Config::get('constants.business'));
        $favorite = 0;


        return response()->json([
            'code' => 0,
            'company' => $business,
            'category' =>$kalit,
            'days' => $days,
            'comments' => $rating,
            'rating' =>$soni,
            'rating_count' =>$count,
            'images' => $images,
            'product' =>$firma2,
            'tags' => $tag,
            'favorite' => $favorite
        ]);
    }

    public function companyView2($id)
    {
        $business = Business::where('id',$id)->first();
        $business->views = $business->views+1;
        $business->save();
        $date=$business->created_at;
        $business->create_date=strtotime($date);
        $business->text = $business->long_title;
        $phones = BusinessContacts::where('business_id',$id)->get();
        foreach ($phones as $item){
            $business->phone.=",".$item->phone;
        }
        unset($business->updated_at,$business->created_at,$business->long_title);
        $rating = Reviews::where('productID',$id)
            ->join('my_user', 'my_reviews.user_id', '=', 'my_user.id')
            ->select('name','rating','comments','date','image')
            ->where('type',Config::get('constants.business'))
            ->get()->toArray();

        $category = BusinessCategory::where('bussiness_id',$id)->get()->toArray();
        $kalit=[];
        foreach ($category as $key) /* Unikal qilib olinyapti */
        {
            if (in_array($key['category_id'], $kalit)) continue;
            else $kalit[]=$key['category_id'];
        }
        $days = BusinessDays::where('business_id',$id)
            ->select('open_time','close_time','day')->get();

        $sum = Reviews::sum($id,Config::get('constants.business'));
        $count = Reviews::count($id,Config::get('constants.business'));
        $images = BusinessImage::where('image_id',$id)
            ->where('type',Config::get('constants.business'))->get();
        $firma = []; $firma2=[];
        $product = Product::where('bussiness_id',$id)->get();
        foreach ($product as $prod){
            $sum2 = Reviews::sum($prod->id,Config::get('constants.product'));
            $count2 = Reviews::count($prod->id,Config::get('constants.product'));
            if ($count2==0) $soni2 = 0;
            else $soni2 = ceil($sum2/$count2);
            $firma['rating']=$soni2;
            $firma['rating_count']=$count2;
            $firma['produkt']=$prod;
            $firma2[]=$firma;
        }
        if ($count==0) $soni = 0;
        else $soni = ceil($sum/$count);
        $tag = TagsAssign::tags($business->id,Config::get('constants.business'));

        $favorite = Favs::where('user_id',Auth::user()->id)
                ->where('business_id',$id)->first();

        if (isset($favorite)) $favorite = 1;
        else $favorite = 0;


        return response()->json([
            'code' => 0,
            'company' => $business,
            'category' =>$kalit,
            'days' => $days,
            'comments' => $rating,
            'rating' =>$soni,
            'rating_count' =>$count,
            'images' => $images,
            'product' =>$firma2,
            'tags' => $tag,
            'favorite' => $favorite
        ]);
    }

    public function companyEdit(Request $request)
    {
//        $this->validate($request,[
//            'user_id' => Auth::user()->id,
//            'address' => 'required|string',
//            'phone' => 'required|int',
//            'email' => 'required|string|email',
//            'website' => 'required',
//            'title'=>'required',
//            'category' => 'required',
//            'description' => 'required',
//            'longitude' => $request->longitude,
//            'latitude' => $request->latitude,
//            'logotype' => 'mimes:png,jpg,jpeg,svg,gif'
//        ]);
        $company = Business::find($request->id);
        if($company->user_id != Auth::user()->id) return response()->json([
            'code' => 1,
            'business_id'=>$company->id,
            'message' => 'Другой пользователь'
        ]);
        $company->update([
            'address' => ($request->address==null)?$company->address:$request->address,
//            'phone' => ($request->phone==null)?$company->phone:$request->phone,
            'website' => ($request->website == null)?$company->website:$request->website,
            'title' => ($request->title==null)?$company->title:$request->title,
            'description' => ($request->description==null)?$company->description:$request->description,
            'long_title' => ($request->text==null)?$company->long_title:$request->text,
            'longitude' => ($request->longitude==null)?$company->longitude:$request->longitude,
            'latitude' => ($request->latitude==null)?$company->latitude:$request->latitude,
            'region_id' => ($request->region_id==null)?$company->region_id:$request->region_id,
            'city_id' => ($request->city_id==null)?$company->city_id:$request->city_id,
            'biz_type' => ($request->biz_type==null)?$company->biz_type:$request->biz_type,
        ]);
        if ($request->hasFile('logotype')) {
            $image = $request->file('logotype');
            $filename = Auth::user()->username."_".time() . '.' . $image->getClientOriginalExtension();
            $location = '../storage/app/public/'. $filename;
//            $location = 'assets/images/user_profile_pic/'. $filename;
            Image::make($image)->save($location);
            $delimage = $company->logotype;
            $pieces = explode("/", $delimage);
            $company->logotype =  "/storage/business/".$filename;
            $company->save();

            $upload = Queque::upload();
            $upload->chdir('business'); // open directory 'test'
            $link = 'https://api.my-city.uz/storage/'.$filename;
            $upload->put($filename, $link,SFTP::SOURCE_LOCAL_FILE);
            $upload->delete($pieces[3]);
            $upload->_disconnect(true);
            @unlink($location);
        }

        $phones = explode(",",$request->phone);
        $bus_phone = BusinessContacts::where('business_id',$company->id)->get();
        foreach ($bus_phone as $busphone){
            $busphone->delete();
        }
        foreach ($phones as $key =>$phone){
            if ($key==0) continue;
            if (isset($phone))
            $phn = BusinessContacts::create([
                'business_id'=>$company->id,
                'phone'=>$phone
            ]);
        }
        if($request->tags!=null){
            $bus_tags = TagsAssign::where('business_id',$company->id)
                ->where('type',Config::get('constants.business'))->get();
            foreach ($bus_tags as $bust)
            {
                $bust->delete();
            }
            $tags = explode(",", $request->tags);
            foreach ($tags as $key => $tag){
                $tag2 = trim($tag);
                $tag_id = Tags::where('name',$tag2)->first();
                if ($tag_id){
                    $assign = TagsAssign::create([
                        'business_id' => $company->id,
                        'tag_id' => $tag_id->tag_id
                    ]);
                }
                else {
                    $biztag = Tags::create([
                        'name' =>$tag2,
                    ]);
                    $assign = TagsAssign::create([
                        'business_id' => $company->id,
                        'tag_id' => $biztag->tag_id
                    ]);
                }
            }
        }
        if ($request->category !=null){
            $bus_cat = BusinessCategory::where('bussiness_id',$company->id)->get();
            foreach ($bus_cat as $busi)
            {
                $busi->delete();
            }

            $str = '$cat='.$request->category.';';
            eval($str);
            foreach ($cat as $item){
                if (!empty($item['sub'])){
                    foreach ($item['sub'] as $sub){
                        $bc = BusinessCategory::create([
                            'bussiness_id'=>$company->id,
                            'category_id'=>$item['cat'],
                            'sub_id'=>$sub,
                        ]);
                    }
                }
                else {
                    $bc = BusinessCategory::create([
                        'bussiness_id'=>$company->id,
                        'category_id'=>$item['cat'],
                        'sub_id'=>0,
                    ]);
                }
            }
        }

        if ($request->days != null){
            $days = BusinessDays::where('business_id',$company->id)->get();
            foreach ($days as $day)
            {
                $day->delete();
            }
            $string = '$sana='.$request->days.';';
            eval($string);  //havfli funksiya ekan
            foreach ($sana as $key => $day)
            {
                $buc = BusinessDays::create([
                    'business_id'=>$company->id,
                    'open_time'=>$day['start'],
                    'close_time'=>$day['end'],
                    'day'=>$day['day'],
                    'status'=>1,
                ]);
            }

        }
        return response()->json([
            'code' => 0,
            'business_id'=>$company->id,
            'message' => 'Бизнесс успешно обновлен'
        ]);
    }

    public function myFavorites()
    {
        $favorite = Favs::where('user_id',Auth::user()->id)->get();
        $res = [];
        foreach ($favorite as $item){
            $res[]=$item->business_id;
        }
        if (!is_array($res)) return response()->json([
            'code' => 0,
            'favorite_business' => $res
        ]);
        $posts = Business::select('id','logotype','address','phone','title','description','created_at','admin_seen')
            ->where('admin_seen',Config::get('constants.admin_seen'))
            ->whereIn('id', $res)
            ->orderBy('id', 'desc')->get()->toArray();
        $res=[];
        foreach ($posts as $item){
//            unset($item['text'],$item['created_at'],$item['updated_at'],$item['longitude'],
//                $item['latitude'],$item['views'],$item['region_id'],$item['city_id'],
//                $item['admin_seen'],$item['user_id'],$item['email']);
            $category = BusinessCategory::where('bussiness_id',$item['id'])->get()->toArray();
            $kalit=[];
            foreach ($category as $key) /* Unikal qilib olinyapti */
            {
                if (in_array($key['category_id'], $kalit)) continue;
                else $kalit[]=$key['category_id'];
            }
            $days = BusinessDays::where('business_id',$item['id'])
                ->select('open_time','close_time','day')->get();
            $sum = Reviews::sum($item['id'],Config::get('constants.business'));
            $count = Reviews::count($item['id'],Config::get('constants.business'));
            if ($count==0) $soni = 0;
            else $soni = ceil($sum/$count);
            $item['category']=$kalit;
            $item['days']=$days;
            $item['rating']= $soni;
            $item['rating_count'] = $count;
            $res[]=$item;
        }
        return response()->json([
            'code' => 0,
            'favorite_business' => $res
        ]);

    }

    public function categoryData(Request $request)
    {
        $cat = BusinessCategory::where('category_id',$request->category)->get()->toArray();
        $result=[];
        foreach ($cat as $items){
            $result[]=$items['bussiness_id'];
        }
        $subcat = Business::whereIn('id', $result)->get()->toArray();
        $res=[]; $uslugi=[];
        foreach ($subcat as $item){
            $date=$item['created_at'];
            unset($item['updated_at']);
            unset($item['created_at']);
            $item['create_date']=strtotime($date);
//            $rating = Reviews::where('productID',$item['id'])->where('type',Config::get('constants.business'))
//                ->select('user_id','comments','date')
//                ->get()->toArray();
            $sum = Reviews::sum($item['id'],Config::get('constants.business'));
            $count = Reviews::count($item['id'],Config::get('constants.business'));
            if ($count==0) $res['rating'] = 0;
            else $res['rating']=ceil($sum/$count);
            $res['rating_count'] = $count;
            $item['rating']= $res;
            $uslugi[]=$item;
        }

        return response()->json([
            'code' => 0,
            'uslugi' => $uslugi,
//            'days' => $days,
        ]);
    }

    public function addReviews(Request $request)
    {
        $business = Business::where('user_id',Auth::user()->id)
            ->where('id',$request->id)->first();
        if (isset($business))
        {
            return response()->json([
                'code' => 1,
                'message' => 'Не нужно себя давать оценки',
            ]);
        }
//        var_dump($request->id);exit('salom');
        $buc = Reviews::create([
            'productID'=>$request->id,
            'user_id'=>Auth::user()->id,
            'rating'=>($request->rating==null)?4:$request->rating,
            'comments'=>($request->comments==null)?" - ":$request->comments,
            'date'=>time(),
            'publish'=>1,
            'type'=>Config::get('constants.business'),
        ]);
        $not = Notification::create([
            'user_id'=>Auth::user()->id,
            'date'=>time(),
            'message'=>'Новый оценка поставлена',
            'category_type'=>Config::get('constants.business'),
            'category_id'=>$request->id,
        ]);
        return response()->json([
           'code' => 0,
           'message' => 'Успешно добавлено',
        ]);
    }

    public function imageUpload(Request $request)
    {
        $image = $request->file('image');
        $filename = Auth::user()->username."_". time() . '.' . $image->getClientOriginalExtension();
        $location = '../storage/app/public/'. $filename;
        Image::make($image)->save($location);
        $buc = BusinessImage::create([
            'image_id'=>$request->business_id,
            'image'=>"/storage/business/".$filename,
            'type'=>Config::get('constants.business'),
        ]);

        $upload = Queque::upload();
        $upload->chdir('business'); // open directory 'test'
        $link = 'https://api.my-city.uz/storage/'.$filename;
        $upload->put($filename, $link,SFTP::SOURCE_LOCAL_FILE);
        $upload->_disconnect(true);
        @unlink($location);
        return response()->json([
            'code' => 0,
            'image' => $filename,
            'message' => 'Success'
        ]);
    }

    public function deleteImage(Request $request)
    {
        $business = BusinessImage::where('type',Config::get('constants.business'))
            ->where('image',$request->image)
            ->where('image_id',$request->business_id)->first();
        if (isset($business)) {
            $upload = Queque::upload();
            $upload->chdir('business'); // open directory 'test'
            $pieces = explode("/", $request->image);
            $upload->delete($pieces[3]);
            $upload->_disconnect(true);
            if ($business->delete()) return response()->json([
                'code' => 0,
                'message' => 'Success'
            ]);
            else return response()->json([
                'code' => 1,
                'message' => 'Не найдено'
            ]);
        }
        else return response()->json([
            'code' => 1,
            'message' => 'Не найдено'
        ]);
    }
    
    public function getTags()
    {
        $tags = Tags::select('name')->get();
        $res='';
        foreach ($tags as $item){
            $res.=$item->name.",";
        }
        return response()->json([
            'code' => 0,
            'tags' => $res
        ]);
    }

}
