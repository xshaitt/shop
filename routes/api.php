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
//商品
Route::get('/goods/list', 'Api\GoodsController@goodsList');
Route::get('/goods/more', 'Api\GoodsController@goodsMore');
Route::get('/goods/detail', 'Api\GoodsController@goodsDetail');
Route::get('/goods_collection/list', 'Api\GoodsController@goodsCollectionList');
Route::get('/goods_collection/cancel', 'Api\GoodsController@cancelGoodsCollection');
Route::post('/goods_collection/add', 'Api\GoodsController@addGoodsCollection');
//收货地址
Route::get('/address/list', 'Api\AddressController@addressList');
Route::get('/address/detail', 'Api\AddressController@addressDetail');
Route::get('/country/list', 'Api\AddressController@countryList');
Route::post('/address/update', 'Api\AddressController@updateAddress');
Route::post('/address/create', 'Api\AddressController@createAddress');
Route::get('/address/setDefault', 'Api\AddressController@setDefaultAddress');
Route::get('/address/default', 'Api\AddressController@addressDefault');

//用户
Route::get('/user/info', 'Api\UserController@userInfo');
Route::post('/user/update', 'Api\UserController@updateUser');

//订单
Route::get('/order/list', 'Api\OrderController@orderList');
Route::post('/order/create', 'Api\OrderController@createOrder');
Route::get('/order/setStatus', 'Api\OrderController@setOrderStatus');
Route::get('/order/delete', 'Api\OrderController@deleteOrder');
Route::get('/order/detail', 'Api\OrderController@orderDetail');