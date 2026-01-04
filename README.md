# WebSocket Chat

Socket.IO を使った簡易チャットアプリ。

## 起動方法

```bash
npm install
npm start
```

## URL

http://localhost:3000/

## 仕様

1.タブを開きユーザー名、パスワ－ドを入力<br>
(ユーザー名はadmin,user1,user2,user3固定)<br>
(パスワードは"password123"固定)<br>
<br>
2.タブを複製し、別のユーザー名、パスワードを入力<br>
<br>
3.メッセージを入力し、送信<br>
<br>
・送信したメッセージ、受信したメッセージ、それぞれの時刻が表示される<br>
・SQLにチャット履歴が保存される<br>
・リロード、タブを閉じた場合ログアウトされる
