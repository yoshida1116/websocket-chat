<?php
declare(strict_types=1);

/**
 * Message モデル
 *
 * messages テーブルに対する
 * ・登録
 * ・取得
 * を責務とするデータアクセスクラス。
 *
 * WebSocket / HTTP 双方から利用されることを想定する。
 */
class Message
{
    /**
     * DB 接続用 PDO インスタンス
     *
     * @var PDO
     */
    private PDO $pdo;

    /**
     * コンストラクタ
     *
     * @param PDO $pdo データベース接続インスタンス
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * メッセージを保存する
     *
     * messages テーブルに 1 件のメッセージを登録する。
     * WebSocket 受信時に呼び出される想定。
     *
     * @param int    $userId     ユーザーID
     * @param string $message    メッセージ本文
     * @param string $sentAt     送信時刻（Y-m-d H:i:s）
     * @param string $receivedAt 受信時刻（Y-m-d H:i:s）
     *
     * @return void
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
     * 最新メッセージ一覧を取得する
     *
     * 指定件数分の最新メッセージを取得し、
     * 表示用に昇順へ並び替えて返却する。
     *
     * @param int $limit 取得件数（デフォルト 50 件）
     *
     * @return array<int, array<string, mixed>>
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
     * 指定ID以降のメッセージを取得する
     *
     * クライアント側の最終取得IDを基準に、
     * 新着メッセージのみを返却する。
     *
     * @param int $lastMessageId 最後に取得したメッセージID
     *
     * @return array<int, array<string, mixed>>
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