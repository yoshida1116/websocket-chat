<?php require __DIR__ . '/../bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ログイン</title>
</head>
<body>
  <h1>ログイン</h1>

  <form id="loginForm">
    <input id="userId" placeholder="ユーザーID" autocomplete="username">
    <input id="password" type="password" placeholder="パスワード" autocomplete="current-password">
    <button type="submit">ログイン</button>
  </form>

  <script src="/js/login.js"></script>
</body>
</html>

  <hr>
  
    <h2>新規ユーザー登録</h2>
    <form id="registerForm">
      <input id="registerUserId" placeholder="新規ユーザーID">
      <input id="registerPassword" type="password" placeholder="新規パスワード">
      <button type="submit">登録</button>
    </form>

  <script src="/js/login.js"></script>
</body>
</html>