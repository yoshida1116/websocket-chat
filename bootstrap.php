<?php
declare(strict_types=1);

/**
 * アプリケーション共通初期化処理
 *
 * - セッション開始
 * - DB 接続取得
 * - 認証チェック
 *
 * 各 PHP ファイルの先頭で require されることを想定する。
 */

// セッション開始
session_start();

/**
 * DB 接続取得
 *
 * database.php から PDO インスタンスを取得する。
 *
 * @var PDO $pdo
 */
$pdo = require __DIR__ . '/app/config/database.php';

/**
 * 現在の実行スクリプトパス取得
 *
 * @var string $path
 */
$path = $_SERVER['SCRIPT_NAME'] ?? '';

/**
 * API リクエスト判定
 *
 * /api 配下はセッション認証チェックを行わない。
 * （ログイン API 等を想定）
 */
if (str_starts_with($path, '/api/')) {
    return;
}

/**
 * 認証チェック
 *
 * 未ログイン状態で login.php 以外にアクセスした場合、
 * ログイン画面へリダイレクトする。
 */
if (!isset($_SESSION['user_id'])) {
    if ($path !== '/login.php') {
        header('Location: /login.php');
        exit;
    }
}