<?php

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


Route::post('/auth', 'LoginController@login');

Route::middleware('checkAdmin')->post('/adverts', 'AdvertsController@store');

Route::middleware('checkAdmin')->post('/adverts/{id}', 'AdvertsController@update');
Route::middleware('checkAdmin')->delete('/adverts/{id}', 'AdvertsController@destroy');

Route::get('/adverts', 'AdvertsController@index');
Route::get('/adverts/{id}', 'AdvertsController@show');

Route::post('/adverts/{id}/comments', 'AdvertsController@addComment');
Route::middleware('checkAdmin')->delete('/adverts/{id}/comments/{commentId}', 'AdvertsController@removeComment');

Route::get('adverts/tag/{tagName}', 'AdvertsController@searchByTag');