<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function logout(Request $request)
    {
        // セッションを全削除
        $request->session()->flush();

        // セッションを再生成（セキュリティ対策）
        $request->session()->regenerate();

        // ログイン画面へリダイレクト
        return redirect('/login');
    }
}
