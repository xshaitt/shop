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

Route::get('documents', 'ApiDoc\ApiDocController@documents');
Route::get('dbStructDocuments', 'ApiDoc\DocumentsController@dbStructDocuments');
Route::get('generateTableStruct', 'ApiDoc\DocumentsController@generateTableStruct');
Route::get('apiDevDocuments', 'ApiDoc\DocumentsController@apiDevDocuments');
Route::get('apiPublicDocuments', 'ApiDoc\DocumentsController@apiPublicDocuments');
Route::get('apiDebug', 'ApiDoc\DocumentsController@apiDebug');
Route::get('generateApiDocuments', 'ApiDoc\GenerateDocumentsController@generateApiDocuments');
Route::get('generateDbDocuments', 'ApiDoc\GenerateDocumentsController@generateDbDocuments');
