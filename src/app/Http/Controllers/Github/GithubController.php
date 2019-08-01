<?php

namespace App\Http\Controllers\Github;

use App\Http\Controllers\Controller;
use Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Aws\S3\S3Client;

use App\User;
use App\Model\Album;
use App\Model\AlbumMember;
use App\Model\AlbumPhoto;

class GithubController extends Controller
{
    // indexを表示(GET)(閲覧)
    public function index() {
        $users = User::all();
        $albums = Album::all();
        $album_members = AlbumMember::all();
        $album_photos = AlbumPhoto::all();
        return view('github', ["users" => $users, "albums" => $albums, "album_members" => $album_members, "album_photos" => $album_photos]); 
    }

    // アルバムを投稿(POST)(アップロード)
    public function createAlbum(Request $request) {
        $album_id = mt_rand();
        $album_name = $request->input('album_name');
        $album_startDate = $request->input('album_startDate');
        $album_endDate = $request->input('album_endDate');

        $album_members = $request->input('album_members');
        foreach ($album_members as $am) {
            AlbumMember::insert(["album_id" => $album_id, "album_member" => $am]); 
        }

        $album_photo = $request->input('album_photo');

        Album::insert(["album_id"  => $album_id, "album_name" => $album_name, "album_startDate" => $album_startDate, "album_endDate" => $album_endDate]); 
        AlbumPhoto::insert(["album_id" => $album_id, "album_photo" => $album_photo]); 
        
        $users = User::all();
        $albums = Album::all();
        $album_members = AlbumMember::all();
        $album_photos = AlbumPhoto::all();
        return view('github', ["users" => $users, "albums" => $albums, "album_members" => $album_members, "album_photos" => $album_photos]); 
    }
    

    public function upload(Request $request)
    {
        $this->validate($request, [
            'file' => [
                // 必須
                'required',
                // アップロードされたファイルであること
                'file',
                // 画像ファイルであること
                'image',
                // MIMEタイプを指定
                'mimes:jpeg,png',
            ]
        ]);

        if ($request->file('file')->isValid([])) {
            $path = $request->file->store('public');
            return view('home')->with('filename', basename($path));
        } else {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors();
        }
    }

    // アルバムを削除(DELETE)(削除)
    public function deleteAlbum(Request $request) { 
    }

    // アルバムを編集(Put)(編集)
    public function putAlbum(Request $request) { 
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