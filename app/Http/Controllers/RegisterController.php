<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\RedirectResponse;

final class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'userId'   => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $exists = User::query()
            ->where('user_id', $request->input('userId'))
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'そのユーザーIDは既に存在します'
            ], 409);
        }

        User::query()->create([
            'user_id'  => $request->input('userId'),
            'password' => Hash::make($request->input('password')),
        ]);

        // 成功メッセージ
        Session::flash('success', '登録完了。ログインしてください。');

        return redirect('/login');
    }
}
