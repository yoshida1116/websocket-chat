<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * ログイン処理コントローラ
 */
final class LoginController extends Controller
{
    /**
     * ログイン処理
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'userId'   => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('user_id', $request->input('userId'))
            ->first();

        if ($user === null) {
            return response()->json([], Response::HTTP_UNAUTHORIZED);
        }

        if (!Hash::check($request->input('password'), $user->password)) {
            return response()->json([], Response::HTTP_UNAUTHORIZED);
        }

        // セッション保存
        session([
            'user_id'   => $user->id,
            'user_name' => $user->user_id,
        ]);

        // CSRF トークン生成
        $csrfToken = Str::random(64);
        session(['csrf_token' => $csrfToken]);

        return response()->json([
            'csrfToken' => $csrfToken,
        ]);
    }
}
