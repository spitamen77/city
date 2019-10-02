<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Date: 03.04.2019 11:52
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 */

namespace App\Http\Controllers\Api;


use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


//use PHPMailer;


class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api')->except(['smsverify','login','signup','forgot','sendwebsms','passwordForgot']);
    }

    protected function credentials(Request $request)
    {
        if(is_numeric($request->get('phone'))){
            return ['phone'=>$request->get('phone'),'password'=>$request->get('password')];
        }
        return ['username'=>$request->get('phone'),'password'=>$request->get('password')];
    }

    public function login(Request $request)
    {
//        $credentials = request(['phone', 'password']);
        $credentials = $this->credentials($request);

        if(!Auth::attempt($credentials))
            return response()->json([
                'user' =>(object)[],
                'code' => 1,
                'message' => 'Неверное телефон или пароль'
            ], 200);

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        $body= $request->user();
        $date = $body->created_at;
        unset($body->group_id,
            $body->user_type,
            $body->forgot,
            $body->view, $body->description,
            $body->updated_at, $body->country,
            $body->created_at,
            $body->tagline,
            $body->lastactive,
            $body->linkedin,
            $body->oauth_provider,
            $body->oauth_uid,
            $body->online, $body->postcode,
            $body->oauth_link,
            $body->notify,
            $body->notify_cat,
            $body->remember_token, $body->facebook,$body->twitter,$body->googleplus,$body->instagram,$body->youtube);
        $body->reg_date = strtotime($date);
        $body->access_token=$tokenResult->accessToken;
        $body->token_type='Bearer';
        $body->expires_at=(int)time() + (7 * 24 * 60 * 60);

        if($body->status == 1)
        {
            return response()->json(
                [
                    'user'=>$request->user(),
                    'code' => 0,
                    'message' => 'Success'
                ]);
        }
        else
        {
            return response()->json(
                [
                    'user'=>$request->user(),
                    'code' => 3,
                    'message' => 'Пожалуйста, подтвердите аккаунт'
                ]);

        }
    }

    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'username' => 'required|string', // |unique:my_user
//            'email' => 'required|string|email',  // |unique:my_user
            'phone' => 'required|string',   // |unique:my_user
            'password' => 'required|string|min:5'
        ]);

        $mail = User::where('username',$request->username)->orWhere('phone',$request->phone)->first();
        if (isset($mail)) return response()->json([
            'code' => 1,
            'message' => 'Такой username/phone уже зарегистрирован'
        ]);
        $vercode=substr(rand(),0,6);

        $user = new User([
            'name' => $request->name,
            'username' => $request->username,
            'email' => null,
            'password_hash' => bcrypt($request->password),
            'phone' =>$request->phone,
            'confirm' => $vercode,
            'status' => 1,  /* bazada 0 bo`lib yoziladi*/
            'user_type' =>'user',
            'view'=>0,
            'image'=>'/storage/profile/default_user.png',
            'country' =>'Uzbekistan'
        ]);
        if ($user->save()){
            $text = 'MyCity.uz || Код подтверждения - '.$vercode;
            Queque::send_sms($request->phone,$text);
        }
        else {
            return response()->json([
                'code' => 2,
                'message' => 'Пользователь не сохранился'
            ]);
        }

        return response()->json([
            'code' => 0,
            'message' => 'Аккаунт успешно создан. Ваш номер отправлен код подтверждения'
        ], 201);
    }

    public function smsverify(Request $request)
    {
        $user = User::where('phone', $request->phone)
            ->where('confirm', $request->vercode)
            ->first();
//        var_dump($request->email);exit($request->vercode);
        if ($user){
            $user['status'] = 2;
            $user['confirm'] = substr(rand(),0,6);
            $user->save();
            return response()->json([
                'code' => 0,
                'message' => 'Проверка кода прошла успешно'
            ]);
        }
        else {
            return response()->json([
                'code' => 1,
                'message' => 'Ошибка проверки кода!'
            ]);
        }
    }

    public function passwordForgot(Request $request)
    {
        $user = User::where('phone', $request->phone)->first();

        if($user){
            $password = Hash::make($request->password);
            $user['password_hash'] = $password;
            $user->save();

            return response()->json([
                'code' => 0,
                'message' => 'Пароль успешно изменен'
            ]);
        }else{
            return response()->json([
                'code' => 1,
                'message' => 'Не найден номер'
            ]);
        }
    }

    public function sendsmsver()   /*Akkauntiga kirib, lekin smsni aktivatsiya qilmagan bo`lsa,*/
    {
        $user = User::find(Auth::id());

        $code = substr(rand(),0,6);
        $user['confirm'] = $code;
        $user->save();
        Queque::send_sms($user->phone,'MyCity.uz || Код подтверждения - '.$code);

        return response()->json([
            'code' => 0,
            'message' => 'Код подтверждения номера успешно отправлен'
        ]);

    }


    public function forgot(Request $request)
    {
        $this->validate($request,[
            'phone' => 'required',
        ]);
        $user = User::where('phone', $request->phone)->first();
        if ($user == null)
        {
            return response()->json([
                'code' => 1,
                'message' => 'Не найден номер'
            ]);
        }
        else
        {
            $to =$user->phone;
            $code = substr(rand(),0,6);

            DB::table('password_resets')->insert(
                ['phone' => $user->phone, 'token' => $code, 'created_at' => date("Y-m-d h:i:s")]
            );
            $user['confirm'] = $code;
            $user->save();
            Queque::send_sms($to, 'MyCity.uz || Код подтверждения - '.$code);

            return response()->json([
                'code' => 0,
                'message' => 'Сброс пароля отправлена на ваш номер'
            ]);
        }

    }

    public function sendwebsms(Request $request)   /* Web saytda reg qilish*/
    {
        $user = User::where('id',$request->user_id)->where('username',$request->login)->first();
        if (isset($user)){
            Queque::send_sms($user->phone,'MyCity.uz || Код подтверждения - '.$user->confirm);

            return response()->json([
                'code' => 0,
                'message' => 'Код подтверждения номера успешно отправлен'
            ]);
        }
        else return response()->json([
            'code' => 1,
            'message' => 'Не найден номер'
        ]);

    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'code' => 0,
            'message' => 'Успешно вышли'
        ]);
    }



}
