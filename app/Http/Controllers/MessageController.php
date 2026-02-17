<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * メッセージ取得コントローラ
 */
final class MessageController extends Controller
{
    /**
     * 最新メッセージ一覧取得
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $limit = min((int) $request->query('limit', 50), 100);

        $messages = Message::query()
            ->select(
                'messages.id',
                'messages.user_id',
                'users.user_id as user_id',
                'messages.message',
                'messages.sent_at',
                'messages.received_at'
            )
            ->join('users', 'users.id', '=', 'messages.user_id')
            ->orderByDesc('messages.id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }

    public function store(Request $request): JsonResponse
    {
        try {
        $validated = $request->validate([
            'user_id'     => ['required', 'string'],
            'message'     => ['required', 'string', 'max:1000'],
            'sent_at'     => ['required', 'date'],
            'received_at' => ['nullable', 'date'],
        ]);

        // 文字列IDからユーザー取得
        $user = User::where('user_id', $validated['user_id'])->first();

        if (!$user) {
            return response()->json([
                'error' => 'ユーザーが存在しません。',
            ], 400);
        }

            $msg = Message::create([
                'user_id'     => $user->id,
                'message'     => $validated['message'],
                'sent_at'     => $validated['sent_at'],
                'received_at' => $validated['received_at'] ?? now(),
            ]);

        return response()->json([
            'id'          => $msg->id,
            'user_id'     => $user->user_id,
            'message'     => $msg->message,
            'sent_at'     => $msg->sent_at,
            'received_at' => $msg->received_at,
        ], 201);

        } catch (\Throwable $e) {

            return response()->json([
                'error' => '送信に失敗しました。',
            ], 500);
        }
    }
}
