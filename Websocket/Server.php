<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

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

final class ChatServer implements MessageComponentInterface
{
    private \SplObjectStorage $clients;
    private ChatService $chatService;

    public function __construct(PDO $pdo)
    {
        $this->clients = new \SplObjectStorage();
        $this->chatService = new ChatService($pdo);
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);
    }

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

            foreach ($this->clients as $client) {
                $client->send(json_encode($result));
            }

        } catch (\Throwable $e) {
            // ★ここで必ず原因が出ます
            error_log(
                "[WS ERROR] " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n",
                3,
                __DIR__ . '/../storage/logs/websocket.log'
            );
            $from->close();
        }
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        $conn->close();
    }
}

$pdo = require __DIR__ . '/../app/config/database.php';

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServer($pdo)
        )
    ),
    8080,
    '127.0.0.1'
);

$server->run();
