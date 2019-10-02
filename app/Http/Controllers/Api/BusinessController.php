<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 19.04.2019 16:14
 */

namespace App\Http\Controllers\Api;

use App\Business;
use App\BusinessCategory;
use App\BusinessDays;
use App\BusinessImage;
use App\Cities;
use App\Http\Controllers\Controller;
use App\Notification;
use App\Product;
use App\Reviews;
use App\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Intervention\Image\Facades\Image;
use phpseclib\Net\SFTP;

class BusinessController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['all','produktView','categoryData','businessSearch']);
    }

    public function all(Request $request)
    {
        $limit = $request->perpage;
        $offset = $request->page-1;
        $produkt= Product::select('id','user_id','admin_seen','bussiness_id','product_name','description','category','sub_category','price','text','type','screen_shot')
        ->where('admin_seen',Config::get('constants.admin_seen'));
        if ($request->perpage && $request->page) $posts = $produkt->skip($offset*$limit)->take($limit);
        $posts = $produkt->orderBy('id', 'desc')->get()->toArray();

        $res=[];
        foreach ($posts as $item) {

            $days = BusinessDays::where('business_id', $item['bussiness_id'])
                ->select('open_time', 'close_time', 'day')->get();
            $sum = Reviews::sum($item['id'], Config::get('constants.product'));
            $count = Reviews::count($item['id'], Config::get('constants.product'));
            if ($count == 0) $soni = 0;
            else $soni = ceil($sum / $count);
            $item['days'] = $days;
            $item['rating'] = $soni;
            $item['rating_count'] = $count;
            $res[] = $item;
        }
        return response()->json([
            'code' => 0,
            'data' => $res
        ]);

    }

    public function produktAdd(Request $request)
    {
        $this->validate($request,[
            'business_id' => 'required',
            'product_name' => 'required',
            'description' => 'required',
            'category' => 'required',
            'sub_category' => 'required',
            'price' => 'required',
//            'phone' => 'required',
            'text' => 'required',
            'type' =>'required',
            'screen_shot' => 'mimes:png,jpg,jpeg,svg,gif',
//            'image' => 'mimes:png,jpg,jpeg,svg,gif'
        ]);
        $biz = Business::where('id',$request->business_id)->where('user_id',Auth::user()->id)->first();
        if (!isset($biz))
            return response()->json([
                'code' => 1,
                'produkt_id' => 0,
                'message' => 'Пользователь такового бизнеса не добавил'
            ]);
        $biz_cat = BusinessCategory::where('bussiness_id',$request->business_id)->where('category_id',$request->category)->first();
        if (!isset($biz_cat))
            return response()->json([
                'code' => 1,
                'produkt_id' => Auth::user()->id,
                'message' => 'Пользователь такового бизнеса не добавил'
            ]);
        if ($request->sub_category == null) return response()->json([
            'code' => 1,
            'produkt_id' => 0,
            'message' => 'Нет саб категория'
        ]);
        $withdraw = Product::create([
            'user_id' => Auth::user()->id,
            'bussiness_id' => $request->business_id,
            'phone' => $biz->phone,
            'product_name' => $request->product_name,
            'description' => $request->description,
            'category' => $request->category,
            'sub_category' => $request->sub_category,
            'long_title' => $request->text,
            'price' => $request->price,
            'type' => $request->type,
            'state' => $biz->region_id,
            'city' => $biz->city_id,
            'views' => 0,
            'slug' => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->product_name))),
        ]);

        if ($request->hasFile('screen_shot')) {
            $business = Product::where('id',$withdraw->id)->first();
            $image = $request->file('screen_shot');
            $filename = Auth::user()->username."_". time() . '.' . $image->getClientOriginalExtension();
            $location = '../storage/app/public/'. $filename;
            Image::make($image)->save($location);
            $business->screen_shot = "/storage/products/".$filename;
            $business->save();

            $upload = Queque::upload();
            $upload->chdir('products'); // open directory 'test'
            $link = 'https://api.my-city.uz/storage/'.$filename;
            $upload->put($filename, $link,SFTP::SOURCE_LOCAL_FILE);
            $upload->_disconnect(true);
            @unlink($location);
        }

        return response()->json([
            'code' => 0,
            'produkt_id' => $withdraw->id,
            'message' => 'Успешно добавлено'
        ]);
    }

    public function produktEdit(Request $request)
    {
        $biz = Business::where('id',$request->business_id)->where('user_id',Auth::user()->id)->first();
        if (!isset($biz))
            return response()->json([
                'code' => 1,
                'produkt_id' => 0,
                'message' => 'Пользователь такового бизнеса не добавил'
            ]);
        $biz_cat = BusinessCategory::where('bussiness_id',$request->business_id)->where('category_id',$request->category)->first();
        if (!isset($biz_cat))
            return response()->json([
                'code' => 1,
                'produkt_id' => 0,
                'message' => 'Пользователь такового бизнеса не добавил'
            ]);
        if ($request->sub_category == null) return response()->json([
            'code' => 1,
            'produkt_id' => 0,
            'message' => 'Нет саб категория'
        ]);
        $company = Product::find($request->id);
        $company->update([
            'business_id' => ($request->business_id==null)?$company->bussiness_id:$request->business_id,
            'phone' => ($request->phone==null)?$company->phone:$request->phone,
            'product_name' => ($request->product_name == null)?$company->product_name:$request->product_name,
            'description' => ($request->description==null)?$company->description:$request->description,
            'category' => ($request->category==null)?$company->category:$request->category,
            'sub_category' => ($request->sub_category==null)?$company->sub_category:$request->sub_category,
            'long_title' => ($request->text==null)?$company->long_title:$request->text,
            'price' => ($request->price==null)?$company->price:$request->price,
            'type' => ($request->type==null)?$company->type:$request->type,
            'state' => $biz->region_id,
            'city' => $biz->city_id,
            'slug' => ($request->product_name==null)?$company->product_name:strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->product_name))),
        ]);
        if ($request->hasFile('screen_shot')) {
            $image = $request->file('screen_shot');
            $filename = Auth::user()->username."_".time() . '.' . $image->getClientOriginalExtension();
            $location = '../storage/app/public/'. $filename;
//            $location = 'assets/images/user_profile_pic/'. $filename;
            Image::make($image)->save($location);
            $delimage = $company->screen_shot;
            $pieces = explode("/", $delimage);
            $company->screen_shot =  "/storage/products/".$filename;
            $company->save();

            $upload = Queque::upload();
            $upload->chdir('products'); // open directory 'test'
            $link = 'https://api.my-city.uz/storage/'.$filename;
            $upload->put($filename, $link,SFTP::SOURCE_LOCAL_FILE);
            $upload->delete($pieces[3]);
            $upload->_disconnect(true);
            @unlink($location);
        }

        return response()->json([
            'code' => 0,
            'produkt_id'=>$company->id,
            'message' => 'Бизнесс успешно обновлен'
        ]);
    }

    public function produktView($id)
    {
        $business = Product::where('id',$id)->first();
        $business->view = $business->view+1;
        $business->save();
        $date=$business->created_at;
        unset($business->updated_at);
        unset($business->created_at);
        $business->create_date=strtotime($date);

//        $days = BusinessDays::where('business_id',$id)
//            ->select('open_time','close_time','day')->get();
        $rating = Reviews::where('productID',$id)
            ->where('type',Config::get('constants.product'))
            ->select('user_id','comments','date')
            ->get()->toArray();
        $sum = Reviews::sum($id, Config::get('constants.product'));
        $count = Reviews::count($id, Config::get('constants.product'));
        $images = BusinessImage::where('image_id',$id)
            ->where('type',Config::get('constants.product'))->get();

        if ($count==0) $soni = 0;
        else $soni = ceil($sum/$count);

        return response()->json([
            'code' => 0,
            'company' => $business,
//            'days' => $days,
            'comments' => $rating,
            'rating' =>$soni,
            'rating_count' =>$count,
            'images' => $images,
        ]);
    }

    public function businessSearch(Request $request)
    {
        $all = Product::where('admin_seen',Config::get('constants.admin_seen'));
        if ($request->category) $all->where('category',$request->category);
        if ($request->sub_category) $all->where('sub_category',$request->sub_category);
        if ($request->search) {
//            $all->where('address', 'LIKE', '%' . $request->search . '%');
            $all->orWhere('product_name','LIKE', '%' . $request->search . '%');
            $all->orWhere('description','LIKE', '%' . $request->search . '%');
            $all->orWhere('text','LIKE', '%' . $request->search . '%');
        }
        $posts = $all->get()->toArray();
//        if ($request->radius) {
//            $lat = 41.2921396;
//            $lng = 69.2311455;
//            $radius = Queque::getRadius($lat, $lng, $request->radius);
//            $compradius = [];
//            foreach ($posts as $item) {
//                $pieces = explode(",", $item['latlong']);
//                //                $pieces[0] = (float)$pieces[0];
//                //                $pieces[1] = (float)$pieces[1];
//                //                $radius['minLat'] = (float)$radius['minLat'];
//                //                $radius['maxLat'] = (float)$radius['maxLat'];
//                //                $radius['minLng'] = (float)$radius['minLng'];
//                //                $radius['maxLng'] = (float)$radius['maxLng'];
//                if ($pieces[0] > $radius['minLat'] && $pieces[0] < $radius['maxLat']) {
//                    if ($pieces[1] > $radius['minLng'] && $pieces[1] < $radius['maxLng']) {
//                        $compradius[] = $item;
//                    }
//                }
//            }
//            return response()->json([
//                'code' => 0,
//                'data' => $compradius
//            ]);
//        }
        return response()->json([
            'code' => 0,
            'data' => $posts
        ]);

    }

    public function categoryData(Request $request)
    {
        $subcat = Product::where('sub_category',$request->subcategory)->get()->toArray();

        $res=[]; $uslugi=[];
        foreach ($subcat as $item){
            $date=$item['created_at'];
            unset($item['updated_at']);
            unset($item['created_at']);
            $item['create_date']=strtotime($date);
//            $rating = Reviews::where('productID',$item['id'])->where('type',Config::get('constants.product'))
//                ->select('user_id','comments','date')
//                ->get()->toArray();
            $sum = Reviews::sum($item['id'], Config::get('constants.product'));
            $count = Reviews::count($item['id'], Config::get('constants.product'));
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
        $business = Product::where('user_id',Auth::user()->id)
            ->where('id',$request->id)->first();
        if (isset($business))
        {
            return response()->json([
                'code' => 1,
                'message' => 'Не нужно себя давать оценки',
            ]);
        }
        $buc = Reviews::create([
            'productID'=>$request->id,
            'user_id'=>Auth::user()->id,
            'rating'=>($request->rating==null)?4:$request->rating,
            'comments'=>($request->comments==null)?" - ":$request->comments,
            'date'=>time(),
            'publish'=>1,
            'type'=>Config::get('constants.product'),
        ]);
        $not = Notification::create([
            'user_id'=>Auth::user()->id,
            'date'=>time(),
            'message'=>'Новый оценка поставлена',
            'category_type'=>Config::get('constants.product'),
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
        $filename = Auth::user()->username."_".time() . '.' . $image->getClientOriginalExtension();
        $location = '../storage/app/public/'. $filename;
        Image::make($image)->save($location);
        $buc = BusinessImage::create([
            'image_id'=>$request->service_id,
            'image'=>"/storage/products/".$filename,
            'type'=>Config::get('constants.product'),
        ]);

        $upload = Queque::upload();
        $upload->chdir('products'); // open directory 'test'
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
        $business = BusinessImage::where('type',Config::get('constants.product'))
            ->where('image',$request->image)
            ->where('image_id',$request->service_id)->first();
        if (isset($business)) {
            $upload = Queque::upload();
            $upload->chdir('products'); // open directory 'test'
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

}
