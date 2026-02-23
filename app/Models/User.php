<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * ID からユーザー取得
     */
    public static function findById(int $id): ?array
    {
        $user = self::query()
            ->select('id', 'user_id', 'created_at')
            ->where('id', $id)
            ->first();

        return $user?->toArray();
    }

    /**
     * user_id から取得（ログイン用）
     */
    public static function findByUserId(string $userId): ?array
    {
        $user = self::query()
            ->select('id', 'user_id', 'password', 'created_at')
            ->where('user_id', $userId)
            ->first();

        return $user?->toArray();
    }
}
