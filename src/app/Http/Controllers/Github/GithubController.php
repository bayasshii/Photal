<?php

namespace App\Http\Controllers\Github;

use Aws\S3\S3Client;

use App\Http\Controllers\Controller;
use Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\User;
use App\Model\Album;
use App\Model\AlbumMember;
use App\Model\AlbumPhoto;

class GithubController extends Controller {
    // s3に画像アップロード
    private function s3upload(int $id, string $image) {
        //拡張子で画像でないファイルをはじく
        // $ext = substr($filename, strrpos($_FILES['img_path']['name'], '.') + 1);
        // if(strtolower($ext) !== 'png' && strtolower($ext) !== 'jpg' && strtolower($ext) !== 'gif'){
        //     echo '画像以外のファイルが指定されています。画像ファイル(png/jpg/jpeg/gif)を指定して下さい';
        //     exit();
        // }
        
        //S3clientのインスタンス生成(各項目の説明は後述)
        $s3client = S3Client::factory([
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'region' => 'ap-northeast-1',
            'version' => 'latest',
        ]);
        //バケット名を指定
        $bucket = getenv('S3_BUCKET_NAME')?: die('No "S3_BUCKET_NAME" config var in found in env!');

        //画像のアップロード(各項目の説明は後述)
        $result = $s3client->putObject([
            'ACL' => 'public-read',
            'Bucket' => $bucket,
            'Key' => $id,
            'Body' => $image,
            'ContentType' =>finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $image)
        ]);
    }

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
        // バリデーションチェック
        $request->validate([
            'album_name' => 'required',
            'album_members' => 'required',
            'album_startDate' => 'required',
            'album_endDate' => 'required',
            'album_files' => 'required'
        ]);

        // albums
        $album_id = mt_rand();
        $album_name = $request->input('album_name');
        $album_startDate = $request->input('album_startDate');
        $album_endDate = $request->input('album_endDate');
        $album_files = $_FILES['album_files']['tmp_name'];
        Album::insert(["album_id"  => $album_id, "album_name" => $album_name, "album_startDate" => $album_startDate, "album_endDate" => $album_endDate]);     
        
        // album_members
        $album_members = $request->input('album_members');
        foreach ($album_members as $am) {
            AlbumMember::insert(["album_id" => $album_id, "album_member" => $am]); 
        }
        
        foreach ($album_files as $af) {
            $album_photo = mt_rand();
            // s3に保存するだけの関数
            $this->s3upload($album_photo, file_get_contents($af));
            AlbumPhoto::insert(["album_id" => $album_id, "album_photo" => $album_photo]); 
        }
        
        $users = User::all();
        $albums = Album::all();
        $album_members = AlbumMember::all();
        $album_photos = AlbumPhoto::all();
        
        return view('github', ["users" => $users, "albums" => $albums, "album_members" => $album_members, "album_photos" => $album_photos]); 
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