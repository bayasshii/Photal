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
    private function s3upload(int $id, str $image) {
        //拡張子で画像でないファイルをはじく
        // $ext = substr($filename, strrpos($_FILES['img_path']['name'], '.') + 1);
        // if(strtolower($ext) !== 'png' && strtolower($ext) !== 'jpg' && strtolower($ext) !== 'gif'){
        //     echo '画像以外のファイルが指定されています。画像ファイル(png/jpg/jpeg/gif)を指定して下さい';
        //     exit();
        // }
        //読み込みの際のキーとなるS3上のファイルパスを作る(作り方は色々あると思います)
        // $tmpname = str_replace('/tmp/','',$_FILES['img_path']['tmp_name']);
        $new_filename = $id;

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
        //アップロードするファイルを用意
        // $image = fopen($_FILES['img_path']['tmp_name'],'rb');

        //画像のアップロード(各項目の説明は後述)
        $result = $s3client->putObject([
            'ACL' => 'public-read',
            'Bucket' => $bucket,
            'Key' => $new_filename,
            'Body' => $image,
            'ContentType' => "image/png",
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
        // albums
        $album_id = mt_rand();
        $album_name = $request->input('album_name');
        $album_startDate = $request->input('album_startDate');
        $album_endDate = $request->input('album_endDate');
        Album::insert(["album_id"  => $album_id, "album_name" => $album_name, "album_startDate" => $album_startDate, "album_endDate" => $album_endDate]);     
        
        // album_members
        $album_members = $request->input('album_members');
        foreach ($album_members as $am) {
            AlbumMember::insert(["album_id" => $album_id, "album_member" => $am]); 
        }
        
        // album_photos
        $album_files = $request->input('album_files');
        
        foreach ($album_files as $af) {
            // s3に保存するだけの関数
            $album_Photo_id = mt_rand();
            $this->s3upload($album_Photo_id, $af);
            AlbumPhoto::insert(["album_id" => $album_id, "album_photo" => $album_photo_id]); 
        }
        
        $users = User::all();
        $albums = Album::all();
        $album_members = AlbumMember::all();
        $album_photos = AlbumPhoto::all();
        
        return view('github', ["users" => $users, "albums" => $albums, "album_members" => $album_members, "album_photos" => $album_photos]); 
    }
    
    
    private function localUpload(Request $request)
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