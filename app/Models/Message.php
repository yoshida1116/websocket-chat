<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'user_id',
        'message',
        'sent_at',
        'received_at',
    ];

    public $timestamps = false;

    /**
     * 最新メッセージ取得
     */
    public static function fetchLatest(int $limit = 50): array
    {
        return self::query()
            ->select(
                'messages.id',
                'messages.user_id',
                'users.user_id as username',
                'messages.message',
                'messages.sent_at',
                'messages.received_at'
            )
            ->join('users', 'users.id', '=', 'messages.user_id')
            ->orderByDesc('messages.id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values()
            ->toArray();
    }

    /**
     * 指定ID以降のメッセージ取得
     */
    public static function fetchAfterId(int $lastMessageId): array
    {
        return self::query()
            ->select(
                'messages.id',
                'messages.user_id',
                'users.user_id as username',
                'messages.message',
                'messages.sent_at',
                'messages.received_at'
            )
            ->join('users', 'users.id', '=', 'messages.user_id')
            ->where('messages.id', '>', $lastMessageId)
            ->orderBy('messages.id')
            ->get()
            ->toArray();
    }
}
