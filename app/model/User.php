<?php
declare(strict_types=1);

/**
 * User モデル
 *
 * users テーブルに対する参照処理を担当するモデルクラス。
 * 認証処理およびユーザー情報取得用途で利用される。
 */
class User
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
     * ID を指定してユーザーを取得する
     *
     * 主キー（id）を条件に users テーブルから
     * ユーザー情報を 1 件取得する。
     *
     * @param int $id ユーザーID（主キー）
     *
     * @return array<string, mixed>|null
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
     * user_id を指定してユーザーを取得する（ログイン用）
     *
     * ログイン認証処理において使用される。
     * password カラムを含めて取得する。
     *
     * @param string $userId ユーザーID（ログインID）
     *
     * @return array<string, mixed>|null
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