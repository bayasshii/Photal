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

        <div class="hoge">
            <form action="/github" method="post">
                <ul>
                    <li>アルバム名 : <input type="text" name="album_name"></li>

                    <li>日にち : <input type="datetime-local" name="album_startDate"> ~ <input type="datetime-local" name="album_endDate"></li>

                    <li>写真 : <input type="text" name="album_photo"></li>
                    
                    <li>メンバー : 
                    <input type="text" name="album_member">
                        <!--
                        <select name="album_members[]" multiple>
                            <option value="1">$user->name</option>
                            <option value="2">ヤッホーーー</option>
                            <option value="3">いえーい</option>
                            @isset($album_member)
                                @foreach($album_member as $am)
                                    <option value="{{ $am->album_member }}">aaa</option>
                                @endforeach
                            @endisset
                        </select>
                        -->
                    </li>

                    <li><input type="submit" value="送信"></li>
                </ul>
                {{ csrf_field() }}
            </form>
        </div>

        <!-- 投稿表示エリア -->
        @isset($album)
            @foreach ($album as $a)
                 <!-- $album に紐つく$album_memberと$album_photoを先に定義したい。でループ回したい。 -->
                
                 <!--
                     
                 -->

                <!-- $album に紐つく$album_memberと$album_photoを先に定義したい。でループ回したい。 -->
                 
                <h2>{{$a->album_name}}</h2>
                <div>{{$a->album_startDate}}</div>
                <div>{{$a->album_endDate}}</div>

                <!--
                @isset($albumMember)
                    @foreach ($albumMember as $am)
                        <div>{{$am->member}}</div>
                    @endforeach
                @endisset

                @isset($albumPhoto)
                    @foreach ($albumPhoto as $ap)
                        <div>{{$ap->photo}}</div>
                    @endforeach
                @endisset
                -->

            @endforeach
        @endisset
    </body>
</html>