<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'my_user';
    public function getAuthPassword() {
        return $this->password_hash;
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password_hash','username', 'user_type','confirm', 'status',
        'forgot', 'sex', 'description','phone','postcode','address','country','city',
        'image','facebook','twitter','instagram','googleplus','youtube','view',
        'remember_token','notify', 'notify_cat','tagline'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password_hash', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        //'phone'=>'int',
        'postcode'=>'int'

    ];

}
