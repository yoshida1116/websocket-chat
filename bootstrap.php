<?php
declare(strict_types=1);

session_start();

// DB 接続
$pdo = require __DIR__ . '/app/config/database.php';

$path = $_SERVER['SCRIPT_NAME'] ?? '';

// API はログインチェックしない
if (str_starts_with($path, '/api/')) {
    return;
}

// 未ログインなら login へ
if (!isset($_SESSION['user_id'])) {
    if ($path !== '/login.php') {
        header('Location: /login.php');
        exit;
    }
}