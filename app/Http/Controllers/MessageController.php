<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Message;
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
        $limit = (int) $request->query('limit', 50);

        $messages = Message::query()
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
            ->values();

        return response()->json($messages);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id'     => 'required|string',
            'message'     => 'required|string',
            'sent_at'     => 'required|string',
            'received_at' => 'nullable|string',
        ]);

        $msg = Message::create([
            'user_id'     => $validated['user_id'],
            'message'     => $validated['message'],
            'sent_at'     => $validated['sent_at'],
            'received_at' => $validated['received_at'] ?? now()->format('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'id' => $msg->id,
            'user_id' => $msg->user_id,
            'message' => $msg->message,
            'sent_at' => $msg->sent_at,
            'received_at' => $msg->received_at,
        ]);
    }
}
