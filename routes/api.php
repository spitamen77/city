<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('auth/index','Api\AuthController@index');
Route::get('users/index', 'Api\UserController@index');
//Route::get('sms/send', 'Api\MessageController@sendSMS');

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'Api\AuthController@login');
    Route::post('signup', 'Api\AuthController@signup');
    Route::post('forgot', 'Api\AuthController@forgot');
    Route::post('smsverify', 'Api\AuthController@smsverify');
    Route::get('sendwebsms', 'Api\AuthController@sendwebsms');
    Route::post('forgot-change', 'Api\AuthController@passwordForgot');

    Route::group([
        'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', 'Api\AuthController@logout');
        Route::get('sendsmsver', 'Api\AuthController@sendsmsver');
    });
});

Route::group([
    'prefix' => 'users'
], function () {
    Route::get('index', 'Api\UserController@index');
    Route::get('data', 'Api\UserController@data');

    Route::group([
        'middleware' => 'auth:api'
    ], function() {
        Route::post('update', 'Api\UserController@update');
        Route::post('password-change', 'Api\UserController@passwordChange');
        Route::post('password-old', 'Api\UserController@passwordOld');
        Route::get('company', 'Api\UserController@product');
        Route::get('getuser', 'Api\UserController@getUser');
        Route::get('favorite', 'Api\CompanyController@myFavorites');
        Route::post('favorite-add', 'Api\UserController@addFavorite');
        Route::get('favorite-delete', 'Api\UserController@delFavorite');
        Route::get('service', 'Api\UserController@userBusiness');  // buni tekshirishim kerak
    });
});

Route::group([
    'prefix' => 'service' // buni adresini ko`rishim kerak. Controller boshqa yozishim kerak
], function () {
    Route::get('all', 'Api\BusinessController@all');
    Route::get('view/{id}', 'Api\BusinessController@produktView');
    Route::get('subcategory', 'Api\BusinessController@categoryData');
    Route::get('search', 'Api\BusinessController@businessSearch');

    Route::group([
        'middleware' => 'auth:api'
    ], function() {
        Route::post('add', 'Api\BusinessController@produktAdd');
        Route::post('add-reviews', 'Api\BusinessController@addReviews');
        Route::post('edit/{id}', 'Api\BusinessController@produktEdit');
        Route::post('image-load', 'Api\BusinessController@imageUpload');
        Route::get('image-delete', 'Api\BusinessController@deleteImage');
    });

});

Route::group([
    'prefix' => 'company'
], function () {
    Route::get('view/{id}', 'Api\CompanyController@companyView');
    Route::get('all', 'Api\CompanyController@businessAll');
    Route::get('category', 'Api\CompanyController@categoryData');
    Route::get('search', 'Api\CompanyController@companySearch');
    Route::get('tags', 'Api\CompanyController@getTags');

    Route::group([
        'middleware' => 'auth:api'
    ], function() {
        Route::post('add', 'Api\CompanyController@companyAdd');
        Route::post('add-reviews', 'Api\CompanyController@addReviews');
        Route::post('edit/{id}', 'Api\CompanyController@companyEdit');
        Route::get('view2/{id}', 'Api\CompanyController@companyView2');
        Route::post('image-load', 'Api\CompanyController@imageUpload');
        Route::get('image-delete', 'Api\CompanyController@deleteImage');
    });
});

Route::group([
    'prefix' => 'data'
], function () {
    Route::get('category', 'Api\DataController@data');
    Route::get('category-data', 'Api\DataController@categoryData');
    Route::get('viloyat', 'Api\DataController@viloyat');
    Route::get('exchange', 'Api\DataController@exchange');  /* Cron uchun */
    Route::get('kurs', 'Api\DataController@kurs');
    Route::get('weather', 'Api\DataController@weather');  /* Cron uchun */
    Route::get('obhavo', 'Api\DataController@obhavo');

    Route::group([
        'middleware' => 'auth:api'
    ], function() {
        Route::get('notification', 'Api\DataController@notification');
    });
});

Route::group([
    'prefix' => 'message'
], function () {

    Route::group([
        'middleware' => 'auth:api'
    ], function() {
        Route::get('all', 'Api\MessagesController@All');
        Route::post('create', 'Api\MessagesController@create');
        Route::get('view/{id}', 'Api\MessagesController@view');
        Route::get('noseen', 'Api\MessagesController@noseen');
    });
});
