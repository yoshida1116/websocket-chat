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
    public function register(Request $request): RedirectResponse
    {
        $request->validate(
            [
                'userId'   => ['required', 'string', 'max:255', 'unique:users,user_id'],
                'password' => ['required', 'string', 'min:8'],
            ],
            [
                'userId.unique' => 'そのユーザーIDは既に存在します',
            ]
        );

        User::create([
            'user_id'  => $request->input('userId'),
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect('/login')
            ->with('success', '登録完了。ログインしてください。');
    }
}
