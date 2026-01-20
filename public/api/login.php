<?php
declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

/**
 * ログイン認証 API
 *
 * JSON 形式で送信されたユーザーID・パスワードを検証し、
 * 認証成功時にセッション情報および CSRF トークンを生成して返却する。
 */

/**
 * JSON リクエストを受信・解析
 *
 * @var array<string, mixed>|null $input
 */
$input = json_decode(file_get_contents('php://input'), true);

/**
 * リクエストパラメータ取得
 *
 * @var string $userId
 * @var string $password
 */
$userId   = $input['userId']   ?? '';
$password = $input['password'] ?? '';

/**
 * 入力値バリデーション
 *
 * 必須項目が不足している場合は 400 Bad Request を返却する。
 */
if ($userId === '' || $password === '') {
    http_response_code(400);
    exit;
}

/**
 * ユーザー取得
 *
 * 指定された user_id をもとに users テーブルから
 * 認証に必要な情報を取得する。
 */
$stmt = $pdo->prepare(
    'SELECT id, password FROM users WHERE user_id = ?'
);
$stmt->execute([$userId]);
$user = $stmt->fetch();

/**
 * ユーザー未存在時の処理
 *
 * 認証失敗として 401 Unauthorized を返却する。
 */
if (!$user) {
    http_response_code(401);
    exit;
}

/**
 * パスワード検証
 *
 * ハッシュ化されたパスワードと入力値を照合する。
 */
if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    exit;
}

/**
 * ログイン成功時の処理
 *
 * 認証済みユーザー情報をセッションに保存する。
 */
$_SESSION['user_id']   = (int)$user['id'];
$_SESSION['user_name'] = $userId;

/**
 * CSRF トークン生成
 *
 * クライアント側（login.js）で保持し、
 * 以降のリクエスト検証に使用する。
 *
 * @var string $csrfToken
 */
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

/**
 * JSON レスポンス返却
 *
 * 認証成功時は CSRF トークンのみ返却する。
 */
header('Content-Type: application/json');
echo json_encode([
    'csrfToken' => $csrfToken,
]);