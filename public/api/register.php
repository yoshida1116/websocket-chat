<?php
declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

$pdo = require __DIR__ . '/../../app/config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

$userId   = $data['userId'] ?? '';
$password = $data['password'] ?? '';

if ($userId === '' || $password === '') {
    http_response_code(400);
    echo 'invalid input';
    exit;
}

// 既存チェック
$stmt = $pdo->prepare('SELECT id FROM users WHERE user_id = :user_id');
$stmt->execute([':user_id' => $userId]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo 'user already exists';
    exit;
}

// 登録
$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare(
    'INSERT INTO users (user_id, password, created_at)
     VALUES (:user_id, :password, NOW())'
);

$stmt->execute([
    ':user_id'  => $userId,
    ':password' => $hash
]);

http_response_code(201);
echo 'ok';
