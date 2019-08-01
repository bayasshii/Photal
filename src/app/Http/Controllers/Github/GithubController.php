<?php

namespace App\Http\Controllers\Github;

use App\Http\Controllers\Controller;
use Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Model\Album;
use App\Model\AlbumMember;
use App\Model\AlbumPhoto;

class GithubController extends Controller
{
    //indexを表示
    public function index() {

        $album = Album::all(); // 全データの取り出し
        $album_member = AlbumPhoto::all(); // 全データの取り出し
        $album_photo = AlbumMember::all(); // 全データの取り出し
        return view('github', ["album" => $album, "album_member" => $album_member, "album_photo" => $album_photo]); 
    }

    // 投稿された内容を表示
    public function createAlbum(Request $request) {

        // 投稿内容の受け取って変数に入れる
        $album_name = $request->input('album_name');
        $album_startDate = $request->input('album_startDate');
        $album_endDate = $request->input('album_endDate');

        $album_id = $request->input('id');
        $album_member = $request->input('album_member');

        $album_photo = $request->input('album_photo');

        Album::insert(["album_name" => $album_name, "album_startDate" => $album_startDate, "album_endDate" => $album_endDate]); 
        AlbumMember::insert(["album_id" => $album_id, "album_member" => $album_member]); 
        AlbumPhoto::insert(["album_id" => $album_id, "album_photo" => $album_photo]); 

        $album = Album::all(); // 全データの取り出し
        $album_member = AlbumPhoto::all(); // 全データの取り出し
        $album_photo = AlbumMember::all(); // 全データの取り出し
        return view('github', ["album" => $album, "album_member" => $album_member, "album_photo" => $album_photo]); // bbs.indexにデータを渡す
    }

    // 名前とかの新規登録画面の表示
    public function top(Request $request)
    {
        $token = $request->session()->get('github_token', null);

        try {
            $github_user = Socialite::driver('github')->userFromToken($token);
        } catch (\Exception $e) {
            return redirect('login/github');
        }

        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', 'https://api.github.com/user/repos', [
            'headers' => [
                'Authorization' => 'token ' . $token
            ]
        ]);

        $app_user = DB::select('select * from public.user where github_id = ?', [$github_user->user['login']]);

        return view('github', [
            'user' => $app_user[0],
            'nickname' => $github_user->nickname,
            'token' => $token,
            'repos' => array_map(function($o) {
                return $o->name;
            }, json_decode($res->getBody()))
        ]);
    }

    public function album(Request $request)
    {

    }
}