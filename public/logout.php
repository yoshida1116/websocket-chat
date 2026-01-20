<?php
declare(strict_types=1);

/**
 * ログアウト処理
 *
 * セッション情報を完全に破棄し、
 * 認証状態を解除した上でログイン画面へリダイレクトする。
 */

// セッション開始
session_start();

/**
 * セッション変数の全削除
 *
 * $_SESSION 配列を初期化し、
 * 保持しているユーザー情報を破棄する。
 */
$_SESSION = [];

/**
 * セッションクッキーの削除
 *
 * セッション ID をブラウザ側からも無効化することで、
 * セッション固定攻撃を防止する。
 */
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

/**
 * セッション破棄
 *
 * サーバー側のセッションデータを完全に削除する。
 */
session_destroy();

/**
 * ログイン画面へリダイレクト
 *
 * 認証解除後は必ずログイン画面へ遷移させる。
 */
header('Location: /login.php');
exit;