<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Chat</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
  <div class="chat-container">
    <div class="chat-header">
      <h1>チャットデモ</h1>
      <button id="logoutBtn" class="logout-btn">ログアウト</button>
    </div>

    <ul id="show-message"></ul>

    <form id="form">
      <input id="message" placeholder="message" autocomplete="off">
      <button type="submit">送信</button>
    </form>
  </div>

  <script>
    window.USER_ID = {{ session('user_id', 0) }};
    window.USER_NAME = "{{ session('user_name', 'Guest') }}";
  </script>
  <script src="/js/chat.js"></script>
  <script src="/js/logout.js"></script>

</body>
</html>
