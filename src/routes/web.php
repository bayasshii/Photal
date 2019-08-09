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

// ---------------------------------------------------
// 以下 ログイン系
// ---------------------------------------------------


Route::get('/', function () {
    return view('welcome');
});

// ログイン機能
Route::get('login/github', 'Auth\LoginController@redirectToProvider');
Route::get('login/github/callback', 'Auth\LoginController@handleProviderCallback');
Route::post('user', 'User\UserController@updateUser');

// Gitログアウト(未実装)
Route::post('/photal/logout', 'Photal\PhotalController@GitLogout');


// ---------------------------------------------------
// 以下 画面表示系
// ---------------------------------------------------


// 画面表示
Route::get('/photal', 'Photal\PhotalController@index');

// リロード対策
Route::get('/photal/home/{id?}','Photal\PhotalController@show');
Route::get('/photal/timeline','Photal\PhotalController@show');


// ---------------------------------------------------
// 以下 非同期通信系
// ---------------------------------------------------

// show画面にLOVEの情報持ってくる(userのgitidと照合して)
Route::post('/api/photal/loveInfo', 'Photal\PhotalController@getLoveInfo');
Route::post('/home/api/photal/loveInfo', 'Photal\PhotalController@getLoveInfo');

// git系の情報
Route::get('/api/photal/github', 'Photal\PhotalController@getGithubInfo');
Route::get('/home/api/photal/github', 'Photal\PhotalController@getGithubInfo');

// アルバムのIDを持ってくる
Route::get('/api/photal/albumId', 'Photal\PhotalController@getAlbumId');
Route::post('/home/api/photalTest', 'Photal\PhotalController@postInfoTest');

// 新規アルバム作る
Route::post('/api/photal', 'Photal\PhotalController@postInfo');

// 写真投稿用(ループするので↑のと切り分けた)
Route::post('/api/photalTest', 'Photal\PhotalController@postInfoTest');
Route::post('/home/api/photalTest', 'Photal\PhotalController@postInfoTest');

// アルバム削除
Route::post('/api/photal/delete', 'Photal\PhotalController@albumDelete');
Route::post('/home/api/photal/delete', 'Photal\PhotalController@albumDelete');

// 良いね
Route::post('/api/photal/love', 'Photal\PhotalController@postLove');
Route::post('/home/api/photal/love', 'Photal\PhotalController@postLove');

// アルバムidをpostして、それに応じた情報色々返してくれる。最強APIなので使いまわしたい。
Route::post('/api/photal/getSelfData', 'Photal\PhotalController@getSelfData');
Route::post('/home/api/photal/getSelfData', 'Photal\PhotalController@getSelfData');

// アルバムの更新
Route::post('/api/photal/upload', 'Photal\PhotalController@albumUpload');
Route::post('/home/api/photal/upload', 'Photal\PhotalController@albumUpload');

// 写真の削除
Route::post('/api/photal/deletePhotos', 'Photal\PhotalController@deletePhotos');
Route::post('/home/api/photal/deletePhotos', 'Photal\PhotalController@deletePhotos');

// home画面(gitのニックネーム取ってくる)
Route::post('/api/photal/home', 'Photal\PhotalController@getHomeInfo');
Route::post('/photal/home/api/photal/home', 'Photal\PhotalController@getHomeInfo');
