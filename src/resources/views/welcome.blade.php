<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="{{ asset('css/hoge.css') }}">
        <title>ログイン画面</title>
    </head>
    <body>
        <div class="loginBtn__container">
            <a 
                class="loginBtn"
                href="/login/github"
            >
                githubアカウントでログイン
            </a>
        </div>
    </body>
</html>