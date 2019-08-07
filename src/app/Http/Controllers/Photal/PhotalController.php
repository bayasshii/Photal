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
        $album = Album::where('album_id',$album_id)->delete();

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
            $this->s3upload($album_photo_id, file_get_contents($af));
            AlbumPhoto::insert(["album_id" => $album_id, "album_photo_id" => $album_photo_id]); 
        }
        return redirect('/photal');
    }

    // ログインとか(GET)
    public function index(Request $request ){
        $albums = Album::orderBy('id','desc')->get();
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

    // アルバム詳細
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

    // home画面
    public function homeIndex(Request $request, $user_id) {
        $albums = Album::orderBy('id','desc')->get();
        $album_members = AlbumMember::all();

        $your_album_members = $album_members->where('album_member', $user_id);

        $album_photos = AlbumPhoto::all();
        
        try {
            $token = $request->session()->get('github_token', null);
            $github_user = Socialite::driver('github')->userFromToken($token);
            $app_users = DB::select('select * from public.user');
        } catch (Exception $e) {
            $github_users = null;
            $app_user = null;
        }
        
        return view('photal/home', [
            'github_user' => $github_user,
            'your_album_members' => $your_album_members,
            "app_users" => $app_users,
            "albums" => $albums,
            "album_members" => $album_members, 
            "album_photos" => $album_photos
        ]);
    }


    // 以下非同期系
    public function getHomeInfo(Request $request) {
        // こっちで、渡すアルバムを指定
        $albums = Album::orderBy('id','desc')->get();
        // $albums->where('member_id', $user_id)->first();
        $album_members = AlbumMember::all();
        $album_photos = AlbumPhoto::all();
        $data = response()->json([
            "albums" => $albums,
            "album_members" => $album_members, 
            "album_photos" => $album_photos
        ]);
        return $data;
    }

    public function getInfo(Request $request) {
        $albums = Album::orderBy('id','desc')->get();
        $album_members = AlbumMember::all();
        $album_photos = AlbumPhoto::all();
        $love_counts = LoveCounts::all();

        try {
            $token = $request->session()->get('github_token', null);
            $github_user = Socialite::driver('github')->userFromToken($token);
            $app_users = DB::select('select * from public.user');
        } catch (Exception $e) {
            $github_users = null;
            $app_user = null;
        }
        $your_love_photos = Loves::where("github_id",$github_user->id)->get();


        $data = response()->json([
            "albums" => $albums,
            "album_members" => $album_members, 
            "album_photos" => $album_photos,
            "app_users" => $app_users,
            "github_user" => $github_user,
            "love_counts" => $love_counts,
            "your_love_photos" => $your_love_photos,
        ]);
        return $data;
    }

    public function postInfo(Request $request) {
        $album_id = $request->album_id;
        $album_name = $request->album_name;
        Album::insert(["album_id"  => $album_id, "album_name" => $album_name]);  

        $album_members = $request->album_members_selected;
        foreach ($album_members as $album_member) {
            AlbumMember::insert(["album_id"  => $album_id, "album_member" => $album_member]);
        }
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

    public function putInfo(Request $request) {
        $album_files = $request->album_files;
        $album_id = $request->album_id;
        
        foreach ($album_files as $af) {
            $album_photo_id = mt_rand();
            // s3に保存するだけの関数
            $this->s3upload($album_photo_id, file_get_contents($af));
            AlbumPhoto::insert(["album_id" => $album_id, "album_photo_id" => $album_photo_id]); 
        }
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

        $albums = Album::orderBy('id','desc')->get();
        $album_name = $albums->where("album_id",$album_id)->first()->album_name;

        Log::info("----------------------");
        Log::info($album_name);
        Log::info("----------------------");

        $album_members = AlbumMember::all();
        $album_photos = AlbumPhoto::all();


        $data = response()->json([
            "album_name" => $album_name,
            "album_members" => $album_members, 
            "album_photos" => $album_photos,
            "app_users" => $app_users,
            "github_user" => $github_user,
            "love_counts" => $love_counts,
            "your_love_photos" => $your_love_photos,
        ]);
        return $data;
    }
}