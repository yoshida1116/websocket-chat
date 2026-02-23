<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\MessageController;

/*
|--------------------------------------------------------------------------
| 認証チェック用ミドルウェア
|--------------------------------------------------------------------------
*/
$authCheck = function ($request, $next) {
    if (!session()->has('user_id')) {
        return redirect('/login');
    }
    return $next($request);
};

/*
|--------------------------------------------------------------------------
| 静的ファイル
|--------------------------------------------------------------------------
*/
Route::get('/css/style.css', function () {
    $path = resource_path('css/style.css');
    abort_unless(File::exists($path), 404);

    return Response::make(File::get($path), 200, [
        'Content-Type' => 'text/css',
    ]);
});

Route::get('/js/{file}', function ($file) {
    $allowed = ['login.js', 'chat.js', 'logout.js'];
    abort_unless(in_array($file, $allowed, true), 404);

    $path = resource_path("js/{$file}");
    abort_unless(File::exists($path), 404);

    return Response::make(File::get($path), 200, [
        'Content-Type' => 'application/javascript',
    ]);
});

/*
|--------------------------------------------------------------------------
| 認証不要ルート
|--------------------------------------------------------------------------
*/
Route::get('/login', function () {
    return view('login');
});

Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/logout', [LogoutController::class, 'logout']);

/*
|--------------------------------------------------------------------------
| 認証必須ルート
|--------------------------------------------------------------------------
*/
Route::middleware('session.auth')->group(function () {

    // トップ画面
    Route::get('/', function () {
        return view('index');
    });

    // メッセージAPI
    Route::get('/api/messages', [MessageController::class, 'index']);
});
