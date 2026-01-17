<?php
declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

/**
 * JSONリクエストを受信
 */
$input = json_decode(file_get_contents('php://input'), true);

$userId   = $input['userId']   ?? '';
$password = $input['password'] ?? '';

if ($userId === '' || $password === '') {
    http_response_code(400);
    exit;
}

/**
 * ユーザー取得
 */
$stmt = $pdo->prepare(
    'SELECT id, password FROM users WHERE user_id = ?'
);
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(401);
    exit;
}

/**
 * パスワード検証
 */
if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    exit;
}

/**
 * ログイン成功 → セッション保存
 */
$_SESSION['user_id']   = (int)$user['id'];
$_SESSION['user_name'] = $userId;

/**
 * CSRFトークン生成（login.js が保存）
 */
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

header('Content-Type: application/json');
echo json_encode([
    'csrfToken' => $csrfToken,
]);
