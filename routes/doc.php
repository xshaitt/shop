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

Route::get('/doc/documents', 'Doc\DocController@documents');
Route::get('/doc/dbStructDocuments', 'Doc\DocController@dbStructDocuments');
Route::get('/doc/generateTableStruct', 'Doc\DocController@generateTableStruct');
Route::get('/doc/apiDevDocuments', 'Doc\DocController@apiDevDocuments');
Route::get('/doc/apiPublicDocuments', 'Doc\DocController@apiPublicDocuments');
Route::get('/doc/apiDebug', 'Doc\DocController@apiDebug');
Route::get('doc/generateApiDocuments', 'Doc\GenerateDocumentsController@generateApiDocuments');
Route::get('doc/generateDbDocuments', 'Doc\GenerateDocumentsController@generateDbDocuments');
