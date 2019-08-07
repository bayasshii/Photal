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
            <button type="submit" class="btn">押すなキケン!ログアウトボタンwill</button>
        </form>

        <a href="/photal/home/{{$github_user->nickname}}">ホームへ</a>

        <!-- 投稿表示 -->
        <div id="timeline">
            <timeline-component/>
        </div>

        <script src="{{ asset('/js/app.js') }}"></script>
    </body>
</html>