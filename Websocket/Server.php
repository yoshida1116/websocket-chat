<?php
declare(strict_types=1);

/**
 * WebSocket サーバ起動スクリプト
 *
 * Ratchet を用いた WebSocket チャットサーバ。
 * - クライアント接続管理
 * - メッセージ受信・配信
 * - DB 永続化
 */

// エラー表示は無効（CLI 実行前提）
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

// オートロード・依存関係読み込み
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/config/database.php';
require __DIR__ . '/../app/model/User.php';
require __DIR__ . '/../app/model/Message.php';
require __DIR__ . '/../app/service/ChatService.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;

/**
 * ChatServer
 *
 * Ratchet 用 WebSocket サーバ実装。
 * 接続管理およびメッセージ配信を担当する。
 */
final class ChatServer implements MessageComponentInterface
{
    /**
     * 接続中クライアント一覧
     *
     * @var \SplObjectStorage<ConnectionInterface>
     */
    private \SplObjectStorage $clients;

    /**
     * チャットユースケース層
     *
     * @var ChatService
     */
    private ChatService $chatService;

    /**
     * コンストラクタ
     *
     * @param PDO $pdo データベース接続インスタンス
     */
    public function __construct(PDO $pdo)
    {
        $this->clients = new \SplObjectStorage();
        $this->chatService = new ChatService($pdo);
    }

    /**
     * クライアント接続時処理
     *
     * @param ConnectionInterface $conn 接続オブジェクト
     *
     * @return void
     */
    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);
    }

    /**
     * メッセージ受信時処理
     *
     * クライアントから送信された JSON メッセージを解析し、
     * DB 保存後、全クライアントへブロードキャストする。
     *
     * @param ConnectionInterface $from 送信元クライアント
     * @param string              $msg  受信メッセージ（JSON）
     *
     * @return void
     */
    public function onMessage(ConnectionInterface $from, $msg): void
    {
        try {
            $data = json_decode($msg, true);
            if (!$data) {
                throw new RuntimeException('Invalid JSON');
            }

            $result = $this->chatService->sendMessage(
                (int)$data['user_id'],
                (string)$data['message'],
                (string)$data['sent_at']
            );

            // 全クライアントへ配信
            foreach ($this->clients as $client) {
                $client->send(json_encode($result));
            }

        } catch (\Throwable $e) {
            /**
             * WebSocket エラー処理
             *
             * 原因は必ずログへ出力し、
             * 問題のある接続は切断する。
             */
            error_log(
                "[WS ERROR] " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n",
                3,
                __DIR__ . '/../storage/logs/websocket.log'
            );

            $from->close();
        }
    }

    /**
     * クライアント切断時処理
     *
     * @param ConnectionInterface $conn 切断された接続
     *
     * @return void
     */
    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);
    }

    /**
     * エラー発生時処理
     *
     * @param ConnectionInterface $conn エラーが発生した接続
     * @param \Exception          $e    例外
     *
     * @return void
     */
    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        $conn->close();
    }
}

/**
 * DB 接続取得
 *
 * database.php から PDO インスタンスを取得する。
 *
 * @var PDO $pdo
 */
$pdo = require __DIR__ . '/../app/config/database.php';

/**
 * WebSocket サーバ起動
 *
 * - ポート: 8080
 * - バインドアドレス: 127.0.0.1
 */
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServer($pdo)
        )
    ),
    8080,
    '127.0.0.1'
);

// 常駐プロセスとして起動
$server->run();