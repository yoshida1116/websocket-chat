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
            ->with('user:id,user_id')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($message) {
                return [
                    'id'          => $message->id,
                    'user_id'     => $message->user_id,
                    'username'    => $message->user->user_id,
                    'message'     => $message->message,
                    'sent_at'     => $message->sent_at,
                    'received_at' => $message->received_at,
                ];
            });

        return response()->json($messages);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'     => 'required|integer',
            'username'    => 'required|string',
            'message'     => 'required|string',
            'sent_at'     => 'required|date',
            'received_at' => 'nullable|date',
        ]);

        $msg = Message::create([
            'user_id'     => $request->user_id,
            'message'     => $request->message,
            'sent_at'     => $request->sent_at,
            'received_at' => $request->received_at ?? now(),
        ]);

        return response()->json($msg);
    }
}
