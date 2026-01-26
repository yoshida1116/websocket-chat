<?php

namespace App\Services;

use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Carbon;
use RuntimeException;

class ChatService
{
    /**
     * メッセージ送信処理
     */
    public function sendMessage(
        int $userId,
        string $messageText,
        string $sentAt
    ): array {
        $receivedAt = Carbon::now();

        // メッセージ保存
        Message::create([
            'user_id'     => $userId,
            'message'     => $messageText,
            'sent_at'     => $sentAt,
            'received_at' => $receivedAt,
        ]);

        // ユーザー取得（表示用）
        $user = User::query()
            ->select('id', 'user_id')
            ->find($userId);

        if (!$user) {
            throw new RuntimeException('User not found');
        }

        return [
            'user_id'     => $userId,
            'username'    => $user->user_id,
            'message'     => $messageText,
            'sent_at'     => $sentAt,
            'received_at' => $receivedAt->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 初期表示用：最新メッセージ取得
     */
    public function getLatestMessages(int $limit = 50): array
    {
        return Message::query()
            ->with('user:id,user_id')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(function ($m) {
                return [
                    'id'          => $m->id,
                    'user_id'     => $m->user_id,
                    'username'    => $m->user->user_id,
                    'message'     => $m->message,
                    'sent_at'     => $m->sent_at,
                    'received_at' => $m->received_at,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * 再接続用：指定ID以降のメッセージ取得
     */
    public function getMessagesAfter(int $lastMessageId): array
    {
        return Message::query()
            ->with('user:id,user_id')
            ->where('id', '>', $lastMessageId)
            ->orderBy('id')
            ->get()
            ->map(function ($m) {
                return [
                    'id'          => $m->id,
                    'user_id'     => $m->user_id,
                    'username'    => $m->user->user_id,
                    'message'     => $m->message,
                    'sent_at'     => $m->sent_at,
                    'received_at' => $m->received_at,
                ];
            })
            ->toArray();
    }
}
