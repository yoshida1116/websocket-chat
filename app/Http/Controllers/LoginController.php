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

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return back()
                ->withErrors([
                    'userId' => 'ユーザーIDまたはパスワードが正しくありません。',
                ])
                ->withInput();
        }

        // セッション保存
        session([
            'user_id'   => $user->id,
            'user_name' => $user->user_id,
        ]);

        return redirect('/')
            ->with('success', 'ログインしました');
    }
}
