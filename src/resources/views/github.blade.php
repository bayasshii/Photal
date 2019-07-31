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
        @empty($user->name)
            <div>
                <form action="/user" method="post">
                    {{ csrf_field() }}

                    <div>お名前 : <input type="text" name="name" value="{{$user->name}}"></div>

                    <div>コメント : <input type="text" name="comment" value="{{$user->comment}}"></div>


                    <input type="submit" value="Confirm">
                </form>
            </div>
        @endisset

        <div class="hoge">
            <form action="/github" method="post">
                <ul>
                    <li>アルバム名 : <input type="text" name="name"></li>

                    <li>日にち : <input type="text" name="date"></li>

                    <li>メンバー : <input type="text" name="member"></li>

                    <li>写真 : <input type="text" name="photo"></li>

                    <li><input type="submit" value="送信"></li>
                </ul>
            </form>
        </div>

        <!-- 投稿表示エリア（編集するのはここ！） -->
        @isset($album)
            @foreach ($album as $a)
                <h2>{{ $a->name }}さんの投稿</h2>
                {{ $d->comment }}
                <br><hr>
            @endforeach
        @endisset
    </body>
</html>