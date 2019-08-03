<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


// ログイン機能
Route::get('login/github', 'Auth\LoginController@redirectToProvider');
Route::get('login/github/callback', 'Auth\LoginController@handleProviderCallback');
Route::post('user', 'User\UserController@updateUser');

// Gitログアウト
Route::post('/photal/logout', 'Photal\PhotalController@GitLogout');

// 投稿機能
Route::get('photal', 'Photal\PhotalController@index');
Route::post('photal', 'Photal\PhotalController@createAlbum');

// アルバム削除機能
Route::post('/photal/delete/{id}', 'Photal\PhotalController@deleteAlbum');

// アルバム写真追加機能
Route::post('/photal/put/{id}', 'Photal\PhotalController@putAlbum');

// アルバム詳細画面
Route::get('/photal/detail/{id}', 'Photal\PhotalController@detailAlbum');

// ajax
Route::get('/contacts', 'ContactController@index');
Route::post('/ajax/contacts', 'Ajax\ContactController@store');
