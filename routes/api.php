<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| WebSocket サーバーやフロントエンドから呼ばれる API 用ルート。
| CSRF は自動的に無効。
|
*/

// メッセージ一覧取得
Route::get('/messages', [MessageController::class, 'index']);

// メッセージ保存（WebSocket → Laravel）
Route::post('/messages', [MessageController::class, 'store']);
