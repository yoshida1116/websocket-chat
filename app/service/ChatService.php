<?php
declare(strict_types=1);

/**
 * ChatService
 * チャット処理のユースケース層
 */
class ChatService
{
    private User $userModel;
    private Message $messageModel;

    public function __construct(PDO $pdo)
    {
        $this->userModel    = new User($pdo);
        $this->messageModel = new Message($pdo);
    }

    /**
     * メッセージ送信処理
     *
     * @param int    $userId
     * @param string $messageText
     * @param string $sentAt クライアント送信日時（Y-m-d H:i:s）
     *
     * @return array 表示用メッセージデータ
     */
    public function sendMessage(
        int $userId,
        string $messageText,
        string $sentAt
    ): array {
        $receivedAt = date('Y-m-d H:i:s');

        // メッセージ保存
        $this->messageModel->create(
            $userId,
            $messageText,
            $sentAt,
            $receivedAt
        );

        // ユーザー取得（表示用）
        $user = $this->userModel->findById($userId);
        if ($user === null) {
            throw new RuntimeException('User not found');
        }

        return [
            'user_id'     => $userId,
            'username'    => $user['user_id'],
            'message'     => $messageText,
            'sent_at'     => $sentAt,
            'received_at' => $receivedAt
        ];
    }

    /**
     * 初期表示用：最新メッセージ取得
     */
    public function getLatestMessages(int $limit = 50): array
    {
        return $this->messageModel->fetchLatest($limit);
    }

    /**
     * 再接続用：指定ID以降のメッセージ取得
     */
    public function getMessagesAfter(int $lastMessageId): array
    {
        return $this->messageModel->fetchAfterId($lastMessageId);
    }
}
