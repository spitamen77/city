<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 26.04.2019 18:08
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Messages;
use App\User;
use http\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['index','businessAll']);
    }

    public function All()
    {
        $all = Messages::where('from_id',Auth::user()->id)
            ->orWhere('to_id',Auth::user()->id)
            ->select('from_id','to_id')
            ->orderBy('message_id', 'desc')
            ->get()->toArray();
        $result = array_unique($all, SORT_REGULAR);
        $massiv=[];
        foreach ($result as $item) /* O`zi yozganlarni chiqarib tashlayapman */
        {
            if ($item['from_id']==Auth::user()->id) unset($item['from_id']);
            if ($item['to_id']==Auth::user()->id) unset($item['to_id']);
            $massiv[]=$item;
        }
        $kalit=[];
        foreach ($massiv as $key) /* Unikal qilib olinyapti */
        {
//            var_dump(Auth::user()->id);exit;
            if (isset($key['from_id'])){
                if (in_array($key['from_id'], $kalit)) continue;
                else if ($key['from_id']!=null) $kalit[]=$key['from_id'];
            }
            else {
                if (in_array($key['to_id'], $kalit)) continue;
                else if ($key['to_id']!=null) $kalit[]=$key['to_id'];
            }
        }
        $object=[];
        foreach ($kalit as $mes) /* Eng so`ngi habarni olyapman*/
        {
            $element=[];
            $message =  Messages::where('from_id',$mes)->where('to_id',Auth::user()->id)->orderBy('message_id', 'desc')->first();
            $message2 = Messages::where('from_id',Auth::user()->id)->where('to_id',$mes)->orderBy('message_id', 'desc')->first();
            $image = User::where('id',$mes)->first();
            if (isset($message)){
                if (isset($message2)){
                    if ($message->message_id > $message2->message_id){
                        $element['seen'] = Messages::where('from_id',Auth::user()->id)->where('to_id',$mes)->where('seen','0')->count();
                        $element['type'] = 'question';
                        $date=$message['message_date'];
                        unset($message['message_date']);
                        unset($message['message_type']);
                        $message['message_date']=strtotime($date);
                        $element['user_id']=$mes;
                        $element['user_image']=$image->image;
                        $element['message']=$message;
                        $object[]=$element;
                    }
                    else {
                        $element['seen'] = Messages::where('from_id',Auth::user()->id)->where('to_id',$mes)->where('seen','0')->count();
                        $element['type'] = 'answer';
                        $date=$message2['message_date'];
                        unset($message2['message_date']);
                        unset($message2['message_type']);
                        $message2['message_date']=strtotime($date);
                        $element['user_id']=$mes;
                        $element['user_image']=$image->image;
                        $element['message']=$message2;
                        $object[]=$element;
                    }
                }
                else {
                    $element['seen'] = Messages::where('from_id',$mes)->where('to_id',Auth::user()->id)->where('seen','0')->count();
                    $element['type'] = 'question';
                    $date=$message['message_date'];
                    unset($message['message_type']);
                    unset($message['message_date']);
                    $message['message_date']=strtotime($date);
                    $element['user_id']=$mes;
                    $element['user_image']=$image->image;
                    $element['message']=$message;
                    $object[]=$element;
                }
            }
            else {
                $element['seen'] = Messages::where('from_id',Auth::user()->id)->where('to_id',$mes)->where('seen','0')->count();
                $element['type'] = 'answer';
                $date=$message2['message_date'];
                unset($message2['message_date']);
                unset($message2['message_type']);
                $message2['message_date']=strtotime($date);
                $element['user_id']=$mes;
                $element['user_image']=$image->image;
                $element['message']=$message2;
                $object[]=$element;
            }
        }

        return response()->json([
            'code' => 0,
            'chat' =>$object,
            'message' => 'Success'
        ]);
    }

    public function create(Request $request)
    {
//        var_dump($request->to_id);exit('aDASDSAD');
//        date_default_timezone_set('Asia/Tashkent');
        $user = User::where('id',$request->to_id)->first();
        if ($request->to_id==Auth::user()->id) return response()->json([
            'code' => 2,
            'message' => 'Ошибка при в вводе'
        ]);
        if (isset($user)){
            $withdraw = Messages::create([
                'from_id' => Auth::user()->id,
                'to_id' => $request->to_id,
                'from_uname' => Auth::user()->name,
                'to_uname' => $user->name,
                'message_content' => $request->message,
                'message_date'=> date("Y-m-d H:i:s"),
            ]);
            return response()->json([
                'code' => 0,
                'message' => 'Success'
            ]);
        }
        return response()->json([
            'code' => 1,
            'message' => 'Ошибка при в вводе'
        ]);
    }

    public function view($id)
    {
        $message = Messages::where([['from_id','=',Auth::user()->id],['to_id','=', $id]])
            ->orWhere([['to_id','=',Auth::user()->id],['from_id','=', $id]])
            ->get();
        $user = User::where('id',$id)->first();
        foreach ($message as $item)
        {
            if ($item->from_id==Auth::user()->id){

                $item->seen=2;
                $item->save();
                $item->message_type="sent";
                $item->seen=1;
            }
            else $item->message_type="received";
            $date=$item->message_date;
            unset($item->message_date);
            $item->message_date=strtotime($date);
        }
        return response()->json([
            'code' => 0,
            'user_name' =>$user->username,
            'user_image' =>$user->image,
            'chat'=> $message,
        ]);
    }

    public function noseen()
    {
        $message = Messages::where('to_id',Auth::user()->id)
                ->where('seen',1)->count();
        return response()->json([
            'code' => 0,
            'no_seen'=> $message,
        ]);
    }
}
