<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="{{ asset('css/hoge.css') }}">
        <title>画像投稿</title>
    </head>
    <body>

        <!-- 投稿表示 -->
        @if(isset($this_album))
            <div><a href="/photal">戻る〜</a></div>
            <div class="album__container">
                @php
                    $albumMembers = $album_members->where('album_id', $this_album->album_id);
                    $albumPhotos = $album_photos->where('album_id', $this_album->album_id);
                @endphp
                <h2>{{$this_album->album_name}}</h2>
                <div>{{$this_album->album_startDate}} ~ {{$this_album->album_endDate}}</div>
                
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
                <form method="post" action="/photal/put/{{$this_album->album_id}}" enctype="multipart/form-data">
                    {{ csrf_field()}}
                    写真追加：
                    <input type="file" class="form-control" name="album_files[]" multiple>
                    <input type="submit" value="送信">
                </form>
                <!--アルバム削除機能-->
                <form method="post" action="/photal/delete/{{$this_album->album_id}}">
                    {{ csrf_field()}}
                    <button type="submit" class="btn">DELETE</button>
                </form>
            </div>
        @else
            <div>アルバムがないみたい！</div>
        @endif
        <script src="{{ asset('/js/app.js') }}"></script>
    </body>
</html>