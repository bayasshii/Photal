<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="{{ asset('css/hoge.css') }}">
        <title>画像投稿</title>
        <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    </head>
    <body>

        <!--プロフィール表示-->
        @if(isset($github_user))
            <div>
                ログイン済みです。<br>
                あなたのお名前 ： {{$github_user->nickname}}<br>
                あなたの画像 ： <img src="https://avatars1.githubusercontent.com/u/{{$github_user->id}}?s=460&v=4">
            </div>
        @else
            <div>
                ログインできてまセーンです。<br>
                あなたのお名前 ：ノーン<br>
                <a href="/login/github">ログインしよう！</a>
            </div>
        @endif
        <form action="/photal/logout" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            <button type="submit" class="btn">押すなキケン</button>
        </form>

        <!-- アルバム新規登録 -->
        <div class="hoge">
            <form action="/photal" method="post" enctype="multipart/form-data">
                <ul>
                    <li>アルバム名 : <input type="text" name="album_name"></li>
                    
                    <li>日にち : 
                    <input type="date" name="album_startDate">
                    ~
                    <input type="date" name="album_endDate"></li>
                    　
                    <li>写真 : 
                        <input type="file" class="form-control" name="album_files[]" multiple>
                    </li>

                    <li>メンバー : 
                        <select name="album_members[]" multiple>
                            @isset($app_users)
                                @foreach($app_users as $u)
                                    <option value="{{ $u->github_id }}">{{ $u->github_id }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </li>

                    <li><input type="submit" value="送信"></li>
                </ul>
                {{ csrf_field() }}
            </form>
        </div>

        <!-- エラーメッセージ -->
        @if ($errors->any())
            <h2>エラーメッセージ</h2>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <!-- 投稿表示 -->
        <div>テストだよん</div>
        <div id="app">
            <showalbum-component></showalbum-component>
        </div>
        @isset($albums)
            @foreach ($albums as $a)
                <div class="album__container">
                    <a href="/photal/detail/{{$a->album_id}}">詳細へ</a>
                    @php
                        $albumMembers = $album_members->where('album_id', $a->album_id);
                        $albumPhotos = $album_photos->where('album_id', $a->album_id);
                    @endphp
                    <h2>{{$a->album_name}}</h2>
                    <div>{{$a->album_startDate}} ~ {{$a->album_endDate}}</div>
                    
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
            @endforeach
        @endisset
        <script src="{{ asset('/js/app.js') }}"></script>
    </body>
</html>