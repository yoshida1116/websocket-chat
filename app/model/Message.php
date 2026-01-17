<?php
declare(strict_types=1);

/**
 * Message モデル
 * messages テーブル専用
 */
class Message
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * メッセージ保存
     */
    public function create(
        int $userId,
        string $message,
        string $sentAt,
        string $receivedAt
    ): void {
        $sql = '
            INSERT INTO messages (
                user_id,
                message,
                sent_at,
                received_at
            ) VALUES (
                :user_id,
                :message,
                :sent_at,
                :received_at
            )
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id'     => $userId,
            ':message'     => $message,
            ':sent_at'     => $sentAt,
            ':received_at' => $receivedAt
        ]);
    }

    /**
     * 最新メッセージ一覧取得
     */
    public function fetchLatest(int $limit = 50): array
    {
        $sql = '
            SELECT
                m.id,
                m.user_id,
                u.user_id AS username,
                m.message,
                m.sent_at,
                m.received_at
            FROM messages m
            INNER JOIN users u ON u.id = m.user_id
            ORDER BY m.id DESC
            LIMIT :limit
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * 指定ID以降のメッセージ取得
     */
    public function fetchAfterId(int $lastMessageId): array
    {
        $sql = '
            SELECT
                m.id,
                m.user_id,
                u.user_id AS username,
                m.message,
                m.sent_at,
                m.received_at
            FROM messages m
            INNER JOIN users u ON u.id = m.user_id
            WHERE m.id > :id
            ORDER BY m.id ASC
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $lastMessageId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
