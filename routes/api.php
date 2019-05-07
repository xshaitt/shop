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
Route::get('/goods/detail', 'Api\GoodsController@goodsDetail');
//收货地址
Route::get('/address/list', 'Api\AddressController@addressList');
Route::get('/address/detail', 'Api\AddressController@addressDetail');
Route::get('/country/list', 'Api\AddressController@countryList');
Route::post('/address/update', 'Api\AddressController@updateAddress');
Route::post('/address/create', 'Api\AddressController@createAddress');
Route::get('/address/setDefault', 'Api\AddressController@setDefaultAddress');


Route::get('documents', 'Doc\ApiDocController@documents');
Route::get('dbStructDocuments', 'Doc\DocumentsController@dbStructDocuments');
Route::get('generateTableStruct', 'Doc\DocumentsController@generateTableStruct');
Route::get('apiDevDocuments', 'Doc\DocumentsController@apiDevDocuments');
Route::get('apiPublicDocuments', 'Doc\DocumentsController@apiPublicDocuments');
Route::get('apiDebug', 'Doc\DocumentsController@apiDebug');
Route::get('generateApiDocuments', 'Doc\GenerateDocumentsController@generateApiDocuments');
Route::get('generateDbDocuments', 'Doc\GenerateDocumentsController@generateDbDocuments');