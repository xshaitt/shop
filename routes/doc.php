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


Route::get('documents', 'Doc\DocController@documents');
Route::get('dbStructDocuments', 'Doc\DocController@dbStructDocuments');
Route::get('generateTableStruct', 'Doc\DocController@generateTableStruct');
Route::get('apiDevDocuments', 'Doc\DocController@apiDevDocuments');
Route::get('apiPublicDocuments', 'Doc\DocController@apiPublicDocuments');
Route::get('apiDebug', 'Doc\DocController@apiDebug');
Route::get('generateApiDocuments', 'Doc\GenerateDocumentsController@generateApiDocuments');
Route::get('generateDbDocuments', 'Doc\GenerateDocumentsController@generateDbDocuments');
