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

Route::get('xsh', function () {
    dd(1);
});

Route::post('/user/register', 'Api\UserController@createUser');
Route::get('/goods/list', 'Api\GoodsController@goodsList');
Route::get('/goods/detail', 'Api\GoodsController@goodsDetail');