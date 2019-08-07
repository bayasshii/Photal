<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="{{ asset('css/hoge.css') }}">
        <title>Home</title>
    </head>
    <body>
        <div>
            ゆああかうんと<br>
            ゆあ名前 ： {{$github_user->nickname}}<br>
            ゆあ画像 ： <img src="https://avatars1.githubusercontent.com/u/{{$github_user->id}}?s=460&v=4">
        </div>
        <div><a href="/photal">戻る〜</a></div>
            @if(isset($albums))
                @foreach ($albums as $a)
                    @foreach ($your_album_members as $yam)
                        @if($yam->album_id == $a->album_id)
                            <div class="album__container">
                                <a href="/photal/detail/{{$a->album_id}}">詳細へ</a>
                                @php
                                    $albumMembers = $album_members->where('album_id', $a->album_id);
                                    $albumPhotos = $album_photos->where('album_id', $a->album_id);
                                @endphp
                                <h2>{{$a->album_name}}</h2>
                                
                                @isset($albumMembers)
                                    <div class="album__membersContainer flex">
                                        @foreach ($albumMembers as $am)
                                        <div class="album__mambersContainer--item">
                                            <a>{{$am->album_member}}</a>
                                        </div>
                                        @endforeach
                                    </div>
                                @endisset

                                @isset($albumPhotos)
                                    <div class="album__imgsContainer">
                                        @foreach ($albumPhotos as $ap)
                                            <div class="album__imgsContainer--item">
                                                <img src="https://bayashi.s3-ap-northeast-1.amazonaws.com/{{$ap->album_photo_id}}">
                                            </div>
                                                @endforeach
                                    </div>
                                @endisset
                                <!--アルバム写真追加機能-->
                                <form method="post" action="/photal/put/{{$a->album_id}}" enctype="multipart/form-data">
                                    {{ csrf_field()}}
                                    写真追加：
                                    <input type="file" class="form-control" name="album_files[]" multiple>
                                    <input type="submit" value="送信">
                                </form>
                                <!--アルバム削除機能-->
                                <form method="post" action="/photal/delete/{{$a->album_id}}">
                                    {{ csrf_field()}}
                                    <button type="submit" class="btn">DELETE</button>
                                </form>
                            </div>
                        @endif
                    @endforeach
                @endforeach
            @else
                <div>
                NoAlbumだよ
                </div>
            @endif
        <script src="{{ asset('/js/app.js') }}"></script>
    </body>
</html>