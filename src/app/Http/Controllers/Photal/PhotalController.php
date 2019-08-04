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
        Album::insert(["album_id"  => $album_id, "album_name" => $album_name, "album_startDate" => $album_startDate, "album_endDate" => $album_endDate]);     
        
        // album_members
        $album_members = $request->input('album_members');
        foreach ($album_members as $am) {
            AlbumMember::insert(["album_id" => $album_id, "album_member" => $am]); 
        }

        // album_photos
        $album_files = $_FILES['album_files']['tmp_name'];
        foreach ($album_files as $af) {
            $album_photo_id= mt_rand();
            // s3に保存するだけの関数
            $this->s3upload($album_photo_id, file_get_contents($af));
            AlbumPhoto::insert(["album_id" => $album_id, "album_photo_id" => $album_photo_id]); 
        }
        
        $users = User::all();
        $albums = Album::all();
        $album_members = AlbumMember::all();
        $album_photos = AlbumPhoto::all();

        try {
            $token = $request->session()->get('github_token', null);
            $github_user = Socialite::driver('github')->userFromToken($token);
            $app_users = DB::select('select * from public.user');
        } catch (Exception $e) {
            $github_user = null;
            $app_users = null;
        }
        
        return view('photal',[
            "users" => $users,
            "albums" => $albums,
            "album_members" => $album_members,
            "album_photos" => $album_photos,
            "github_user"=>$github_user,
            "app_users"=>$app_users
        ]); 
    }
    
    public function deleteAlbum($album_id){
        $albums = Album::all();
        Log::info($album_id);
        $album = Album::where('album_id',$album_id)->delete();
        // Album::destroy($album->id);
        Log::info($album);
        \Session::flash('flash_message', '削除しました。');
        
        $users = User::all();
        $albums = Album::all();
        $album_members = AlbumMember::all();
        $album_photos = AlbumPhoto::all();
        return redirect('/photal');
    }

    // アルバムに写真追加(Put)(編集)
    public function putAlbum(Request $request, $album_id) {
        // バリデーションチェック
        $request->validate([
            'album_files' => 'required'
        ]);
        
        $album_files = $_FILES['album_files']['tmp_name'];
        foreach ($album_files as $af) {
            $album_photo_id = mt_rand();
            // s3に保存するだけの関数
            $this->s3upload($album_photo, file_get_contents($af));
            AlbumPhoto::insert(["album_id" => $album_id, "album_photo_id" => $album_photo_id]); 
        }
        return redirect('/photal');
    }

    // ログインとか(GET)
    public function index(Request $request ){
        $albums = Album::all();
        $album_members = AlbumMember::all();
        $album_photos = AlbumPhoto::all();
            
        try {
            $token = $request->session()->get('github_token', null);
            $github_user = Socialite::driver('github')->userFromToken($token);
            $app_users = DB::select('select * from public.user');
        } catch (Exception $e) {
            $github_users = null;
            $app_user = null;
        }
        
        return view('photal', [
            'github_user' => $github_user,
            "app_users" => $app_users,
            "albums" => $albums,
            "album_members" => $album_members, 
            "album_photos" => $album_photos
        ]);
    }

    public function gitLogout(Request $request) {
        Photal::logout();
        return redirect('photal');
    }

    public function detailAlbum(Request $request, $album_id) {
        $albums = Album::all();
        $album_members = AlbumMember::all();
        $album_photos = AlbumPhoto::all();
        
        $this_album = $albums->where('album_id', $album_id)->first();
        
        try {
            $token = $request->session()->get('github_token', null);
            $github_user = Socialite::driver('github')->userFromToken($token);
            $app_users = DB::select('select * from public.user');
        } catch (Exception $e) {
            $github_users = null;
            $app_user = null;
        }
        
        return view('photal/detail', [
            'this_album' => $this_album,
            'github_user' => $github_user,
            "app_users" => $app_users,
            "albums" => $albums,
            "album_members" => $album_members, 
            "album_photos" => $album_photos
        ]);
    }

    public function get_api(Request $request) {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With");

        $lastName = $request->last_name;
        $lastName = $lastName . $lastName;

        $albums = Album::all();
        $album_members = AlbumMember::all();
        $album_photos = AlbumPhoto::all();
        // $album_id = $request->album_id;
        // $album_id = $album_id;
        // $data = response()->json(['album_id'=>$album_id]);
        $data = response()->json([
            'lastName'=>$lastName, 
            "albums" => $albums,
            "album_members" => $album_members, 
            "album_photos" => $album_photos]);
        return $data;
    }
}