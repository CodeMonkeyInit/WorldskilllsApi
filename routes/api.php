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

Route::middleware('checkAdmin')->post('/posts', 'PostsController@store');

Route::middleware('checkAdmin')->post('/posts/{id}', 'PostsController@update');
Route::middleware('checkAdmin')->delete('/posts/{id}', 'PostsController@destroy');

Route::get('/posts', 'PostsController@index');
Route::get('/posts/{id}', 'PostsController@show');

Route::post('/posts/{id}/comments', 'PostsController@addComment');
Route::post('/posts/{id}/comments/{commentId}', 'PostsController@removeComment');



