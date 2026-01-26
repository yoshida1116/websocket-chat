# WebSocket Chat

Socket.IO を使った簡易チャットアプリ。

## 起動方法

```bash
Ratchetを入れる
composer require cboden/ratchet

ターミナル　プロジェクトフォルダで
php Websocket/server.php

ターミナル　別のプロジェクトフォルダで
php -S localhost:3000 -t public
```

## URL

http://localhost:3000/login.php

## 仕様

1.タブを開きユーザー名を入力<br>
2.タブを複製し、別のユーザー名を入力<br>
3.メッセージを入力し、送信<br>

・送信したメッセージ、受信したメッセージ、それぞれの時刻が表示される<br>
