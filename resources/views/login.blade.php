<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
  <h1>ログイン</h1>

    <form id="loginForm" method="POST" action="{{ url('/login') }}">
        @csrf
        <input id="userId" name="userId" placeholder="ユーザーID">
        <input id="password" name="password" type="password" placeholder="パスワード">
        <button type="submit">ログイン</button>
    </form>

    <h2>新規ユーザー登録</h2>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="color:red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form id="registerForm" method="POST" action="{{ url('/register') }}">
        @csrf
        <input id="registerUserId" name="userId" placeholder="新規ユーザーID">
        <input id="registerPassword" name="password" type="password" placeholder="新規パスワード">
        <button type="submit">登録</button>
    </form>

</body>
</html>
