<?php
declare(strict_types=1);

/**
 * ChatService
 *
 * チャット機能に関するユースケース層（Service）。
 * Controller / WebSocket ハンドラから呼び出され、
 * ユーザー取得・メッセージ永続化・表示用データ生成を統括する。
 */
class ChatService
{
    /**
     * ユーザーモデル
     *
     * @var User
     */
    private User $userModel;

    /**
     * メッセージモデル
     *
     * @var Message
     */
    private Message $messageModel;

    /**
     * コンストラクタ
     *
     * 同一 PDO を用いて各モデルを初期化する。
     *
     * @param PDO $pdo データベース接続インスタンス
     */
    public function __construct(PDO $pdo)
    {
        $this->userModel    = new User($pdo);
        $this->messageModel = new Message($pdo);
    }

    /**
     * メッセージ送信処理
     *
     * 受信日時をサーバー側で確定させた上でメッセージを保存し、
     * クライアント表示用の配列を生成して返却する。
     *
     * @param int    $userId      ユーザーID
     * @param string $messageText メッセージ本文
     * @param string $sentAt      クライアント送信日時（Y-m-d H:i:s）
     *
     * @return array<string, mixed> 表示用メッセージデータ
     *
     * @throws RuntimeException ユーザーが存在しない場合
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

        // 表示用ユーザー情報取得
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
     * 初期表示用：最新メッセージを取得する
     *
     * 画面初期表示時に使用される。
     *
     * @param int $limit 取得件数
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLatestMessages(int $limit = 50): array
    {
        return $this->messageModel->fetchLatest($limit);
    }

    /**
     * 再接続用：指定 ID 以降のメッセージを取得する
     *
     * WebSocket 再接続時やポーリング時に使用される。
     *
     * @param int $lastMessageId 最後に取得したメッセージID
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMessagesAfter(int $lastMessageId): array
    {
        return $this->messageModel->fetchAfterId($lastMessageId);
    }
}