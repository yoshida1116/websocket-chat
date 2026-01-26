<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\MessageController;

Route::get('/css/style.css', function () {
    $path = resource_path('css/style.css');

    if (!File::exists($path)) {
        abort(404);
    }

    return Response::make(File::get($path), 200, [
        'Content-Type' => 'text/css',
    ]);
});

// ログイン画面
Route::get('/login', function () {
    return view('login');
});

// ログアウト
Route::get('/logout', [LogoutController::class, 'logout']);

// JS
Route::get('/js/login.js', function () {
    $path = resource_path('js/login.js');

    if (!File::exists($path)) {
        abort(404);
    }

    return Response::make(File::get($path), 200, [
        'Content-Type' => 'application/javascript',
    ]);
});

// chat.js
Route::get('/js/chat.js', function () {
    $path = resource_path('js/chat.js');

    if (!File::exists($path)) {
        abort(404);
    }

    return Response::make(File::get($path), 200, [
        'Content-Type' => 'application/javascript',
    ]);
});

// logout.js
Route::get('/js/logout.js', function () {
    $path = resource_path('js/logout.js');

    if (!File::exists($path)) {
        abort(404);
    }

    return Response::make(File::get($path), 200, [
        'Content-Type' => 'application/javascript',
    ]);
});

// ログイン処理
Route::post('/login', [LoginController::class, 'login']);

// 登録処理
Route::post('/register', [RegisterController::class, 'register']);

// index
Route::get('/', function () {
    if (!session()->has('user_id')) {
        return redirect('/login');
    }
    return view('index');
});

// メッセージ取得
Route::get('/messages', [MessageController::class, 'index']);

// メッセージ保存
Route::post('/messages', [MessageController::class, 'store']);
