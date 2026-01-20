<?php
declare(strict_types=1);

/**
 * データベース接続設定
 *
 * MySQL への接続に必要な基本情報を定義する。
 * 本設定は PDO 接続文字列（DSN）生成に使用される。
 */
$host     = '127.0.0.1';
$dbname   = 'chat';
$user     = 'root';
$password = 'root';
$charset  = 'utf8mb4';

/**
 * PDO 用 DSN（Data Source Name）
 *
 * @var string $dsn
 */
$dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

try {
    /**
     * PDO インスタンス生成
     *
     * - エラーモード：例外
     * - フェッチモード：連想配列
     * - プリペアドステートメントのエミュレーション無効
     *
     * @var PDO $pdo
     */
    $pdo = new PDO(
        $dsn,
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    /**
     * DB 接続エラー時の処理
     *
     * エラーメッセージをログファイルに出力し、
     * 呼び出し元でハンドリングできるよう例外を再送出する。
     */
    error_log(
        '[DB ERROR] ' . $e->getMessage() . PHP_EOL,
        3,
        __DIR__ . '/../../storage/logs/db.log'
    );

    throw $e;
}

/**
 * PDO インスタンスを返却
 *
 * require / include された側で DB 接続を利用可能とする。
 *
 * @return PDO
 */
return $pdo;