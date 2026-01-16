<?php
declare(strict_types=1);

/**
 * User モデル
 * users テーブル専用
 */
class User
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * ID からユーザー取得
     */
    public function findById(int $id): ?array
    {
        $sql = '
            SELECT
                id,
                user_id,
                created_at
            FROM users
            WHERE id = :id
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user !== false ? $user : null;
    }

    /**
     * user_id から取得（ログイン用）
     */
    public function findByUserId(string $userId): ?array
    {
        $sql = '
            SELECT
                id,
                user_id,
                password,
                created_at
            FROM users
            WHERE user_id = :user_id
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user !== false ? $user : null;
    }
}
