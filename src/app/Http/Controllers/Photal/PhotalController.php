<?php

namespace App\Http\Controllers\Photal;

use Aws\S3\S3Client;

use App\Http\Controllers\Controller;
use Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Model\User;
use App\Model\Album;
use App\Model\AlbumMember;
use App\Model\AlbumPhoto;

use App\Model\Loves;
use App\Model\LoveCounts;

use Illuminate\Support\Facades\Log;

class PhotalController extends Controller {
    // s3に画像アップロードするローカル関数
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

    // 初期画面の表示
    public function index(Request $request ){
        return view('photal');
    }

    // リロード対策
    public function show(Request $request) {
        return view('photal');
    }

    public function gitLogout(Request $request) {
        Photal::logout();
        return redirect('photal');
    }

    // 以下非同期系
    public function getLoveInfo(Request $request) {
        $github_id = $request->github_id;
        $love_counts = LoveCounts::all();
        $your_love_photos = Loves::where("github_id", $github_id)->get();
        $data = response()->json([
            "love_counts" => $love_counts,
            "your_love_photos" => $your_love_photos,
        ]);
        return $data;
    }

    public function getGithubInfo(Request $request) {
        try {
            $token = $request->session()->get('github_token', null);
            $github_user = Socialite::driver('github')->userFromToken($token);
            $app_users = DB::select('select * from public.user');
        } catch (Exception $e) {
            $github_users = null;
            $app_user = null;
        }
        $data = response()->json([
            "app_users" => $app_users,
            "github_user" => $github_user
        ]);
        return $data;
    }

    public function postInfo(Request $request) {
        $album_id = $request->album_id;
        $album_name = $request->album_name;
        $nickname = $request->nickname;
        Album::insert(["album_id"  => $album_id, "album_name" => $album_name]);

        $album_members = $request->album_members_selected;
        foreach ($album_members as $album_member) {
            AlbumMember::insert(["album_id"  => $album_id, "album_member" => $album_member]);
        }
        AlbumMember::insert(["album_id"  => $album_id, "album_member" => $nickname]);
    }

    public function postInfoTest(Request $request) {
        $album_photo = $request->image;
        $album_id = $request->album_id;
        $album_photo_id = mt_rand();
        AlbumPhoto::insert(["album_id" => $album_id, "album_photo_id" => $album_photo_id]);
        LoveCounts::insert(["album_photo_id" => $album_photo_id, "love_count" => 0]);
        $this->s3upload($album_photo_id, file_get_contents($album_photo));
    }

    public function postLove(Request $request) {
        $album_photo_id = $request->album_photo_id;
        $github_id = $request->github_id;

        // $love->photo_id に 一致するカラムがあるか
        $this_photo = Loves::all()->where('album_photo_id',$album_photo_id)->where("github_id",$github_id)->first();

        // なければlike判定で
        if(empty($this_photo)) {
            Loves::insert(["album_photo_id" => $album_photo_id, "github_id" => $github_id]);
        }
        // あればdislike判定
        else {
            Loves::where('album_photo_id',$album_photo_id)->where("github_id",$github_id)->first()->delete();
        }

        $this_loveCount = LoveCounts::all()->where("album_photo_id",$album_photo_id)->first();
        $this_loveCount->love_count = Loves::where("album_photo_id",$album_photo_id)->count();
        $this_loveCount->save();

        $your_love_photos = Loves::where("github_id", $github_id)->get();

        $love_counts = LoveCounts::all();
        $data = response()->json([
            "your_love_photos" => $your_love_photos,
            "love_counts" => $love_counts
        ]);
        return $data;
    }

    public function albumDelete(Request $request) {
        $album_id = $request->album_id;
        Album::where('album_id',$album_id)->delete();
        AlbumMember::where('album_id',$album_id)->delete();
        AlbumPhoto::where('album_id',$album_id)->delete();
    }

    // album_idから色々返してくれる
    public function getSelfData(Request $request) {
        $album_id = $request->album_id;

        $album_name = Album::where("album_id", $album_id)->first()->album_name;

        $album_members = AlbumMember::where("album_id",$album_id)->get();

        $album_photos = AlbumPhoto::where("album_id",$album_id)->get();

        try {
            $token = $request->session()->get('github_token', null);
            $github_user = Socialite::driver('github')->userFromToken($token);
            $app_users = DB::select('select * from public.user');
        } catch (Exception $e) {
            $github_users = null;
            $app_user = null;
        }

        $data = response()->json([
            "album_name" => $album_name,
            "album_members" => $album_members,
            "album_photos" => $album_photos,
            "app_users" => $app_users,
            "github_user" => $github_user
        ]);
        return $data;
    }

    public function albumUpload(Request $request) {

        $album_id = $request->album_id;
        $album_name = $request->album_name;
        $album_members = $request->album_members;
        $nickname = $request->nickname;

        // タイトル変更
        $Album = Album::where("album_id",$album_id)->first();
        $Album->album_name = $album_name;
        $Album->save();

        try {
            $token = $request->session()->get('github_token', null);
            $github_user = Socialite::driver('github')->userFromToken($token);
        }catch (Exception $e) {
            $github_users = null;
        }

        // メンバー変更
        AlbumMember::where("album_id", $album_id)->delete();
        foreach ($album_members as $album_member) {
            AlbumMember::insert(["album_id"  => $album_id, "album_member" => $album_member]);
        }
        // AlbumMember::where("album_member",$github_user->nickname)->first();
        AlbumMember::insert(["album_id"  => $album_id, "album_member" => $github_user->nickname]);
    }

    public function deletePhotos(Request $request) {
        // 写真のID
        $album_delete_photos = $request->album_delete_photos;
        // 写真削除(s3からは消せてない)
        foreach ($album_delete_photos as $album_photo_id) {
            AlbumPhoto::where("album_photo_id", $album_photo_id)->delete();
        }
    }

    public function getAlbumId(Request $request) {
        $albums = Album::all();
        $data = response()->json([
            "albums" => $albums
        ]);
        return $data;
    }

    public function getHomeInfo(Request $request) {
        $nickname = $request->nickname;
        Log::info($nickname);
        $album_members = AlbumMember::where("album_member", $nickname)->get();

        $data = response()->json([
            "album_members" => $album_members,
        ]);
        return $data;
    }
}
