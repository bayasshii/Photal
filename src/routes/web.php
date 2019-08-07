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

// Gitログアウト(未実装)
Route::post('/photal/logout', 'Photal\PhotalController@GitLogout');

// 投稿機能
Route::get('photal', 'Photal\PhotalController@index');
Route::post('photal', 'Photal\PhotalController@createAlbum');

// アルバム削除機能
Route::post('/photal/delete/{id}', 'Photal\PhotalController@deleteAlbum');

// アルバム写真追加機能
Route::post('/photal/put/{id}', 'Photal\PhotalController@putAlbum');

// アルバム詳細画面
Route::get('/photal/detail/{albam_id}', 'Photal\PhotalController@detailAlbum');

// ホーム画面
Route::get('photal/home/{user_id}', 'Photal\PhotalController@homeIndex');

// 非同期通信
Route::get('/api/photal', 'Photal\PhotalController@getInfo');
Route::post('/api/photal', 'Photal\PhotalController@postInfo');

// アルバム削除
Route::post('/api/photal/delete', 'Photal\PhotalController@albumDelete');

// 良いねしたり、新規作成したり
Route::post('/api/photalTest', 'Photal\PhotalController@postInfoTest');
Route::post('/api/photal/love', 'Photal\PhotalController@postLove');

// アルバムid入れたらいろんな情報返してくれる最強API
Route::post('/api/photal/getSelfData', 'Photal\PhotalController@getSelfData');

// アルバムの更新
Route::post('/api/photal/upload', 'Photal\PhotalController@albumUpload');
// 写真の削除
Route::post('/api/photal/deletePhotos', 'Photal\PhotalController@deletePhotos');

Route::post('/api/photal/put', 'Photal\PhotalController@putInfo');
Route::get('/api/photal/home', 'Photal\PhotalController@getHomeInfo');