<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="{{ asset('css/hoge.css') }}">
        <title>画像投稿</title>
    </head>
    <body>
        <!--名前登録(もしまだ登録してなかったら)-->
        @isset($user)
            @empty($user->name)
                <div>
                    <form action="/user" method="post">
                        {{ csrf_field() }}
                        
                        <div>お名前 : <input type="text" name="name" value="{{$user->name}}"></div>
                        
                        <div>コメント : <input type="text" name="comment" value="{{$user->comment}}"></div>
                        
                        <input type="submit" value="Confirm">
                    </form>
                </div>
            @endempty
        @endisset

         <!-- アルバム新規登録 -->
        <div class="hoge">
            <form action="/github" method="post" enctype="multipart/form-data">
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
                            @isset($users)
                                @foreach($users as $u)
                                    <option value="{{ $u->name }}">{{ $u->name }}</option>
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
        @isset($albums)
            @foreach ($albums as $a)
                <div class="album__container">
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
                                    <img src="https://bayashi.s3-ap-northeast-1.amazonaws.com/{{$ap->album_photo}}">
                                </div>
                                    @endforeach
                        </div>
                    @endisset
                </div>
            @endforeach
        @endisset
    </body>
</html>