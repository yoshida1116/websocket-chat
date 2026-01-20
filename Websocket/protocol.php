<?php
declare(strict_types=1);

/**
 * WebSocket プロトコル処理ユーティリティ
 *
 * RFC6455 に準拠した最低限の WebSocket 処理を提供する。
 * - ハンドシェイク
 * - フレームデコード（Client → Server）
 * - フレームエンコード（Server → Client）
 *
 * ※ 制御フレーム（close / ping / pong）は未対応
 */

/**
 * WebSocket ハンドシェイク処理
 *
 * クライアントからの HTTP Upgrade リクエストを解析し、
 * WebSocket 接続確立のためのレスポンスを返却する。
 *
 * @param resource $client  クライアントソケット
 * @param string   $request クライアントからの生リクエスト
 *
 * @return void
 *
 * @throws RuntimeException ハンドシェイク不正時
 */
function websocket_handshake($client, string $request): void
{
    if (!preg_match('/Sec-WebSocket-Key:\s*(.+)\r\n/i', $request, $matches)) {
        throw new RuntimeException('Invalid WebSocket handshake request');
    }

    $key = trim($matches[1]);

    /**
     * Sec-WebSocket-Accept 生成
     *
     * クライアントキー + GUID を SHA-1 でハッシュ化し、
     * Base64 エンコードする。
     */
    $acceptKey = base64_encode(
        sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true)
    );

    $response =
        "HTTP/1.1 101 Switching Protocols\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "Sec-WebSocket-Accept: {$acceptKey}\r\n\r\n";

    socket_write($client, $response);
}

/**
 * WebSocket フレームをデコードする
 *
 * クライアントから送信される WebSocket フレームは
 * 必ずマスクされているため、マスク解除を行う。
 *
 * @param string $data 受信した生フレームデータ
 *
 * @return string デコード後のペイロード（テキスト）
 */
function websocket_decode(string $data): string
{
    $length = ord($data[1]) & 127;

    if ($length === 126) {
        $mask    = substr($data, 4, 4);
        $payload = substr($data, 8);
    } elseif ($length === 127) {
        $mask    = substr($data, 10, 4);
        $payload = substr($data, 14);
    } else {
        $mask    = substr($data, 2, 4);
        $payload = substr($data, 6);
    }

    $text = '';
    $payloadLength = strlen($payload);

    /**
     * マスク解除処理
     *
     * payload[i] XOR mask[i % 4]
     */
    for ($i = 0; $i < $payloadLength; $i++) {
        $text .= $payload[$i] ^ $mask[$i % 4];
    }

    return $text;
}

/**
 * WebSocket フレームをエンコードする
 *
 * サーバーからクライアントへの送信フレームは
     * マスクを行わない（RFC6455 準拠）。
 *
 * @param string $payload 送信するテキストデータ
 *
 * @return string エンコード済み WebSocket フレーム
 */
function websocket_encode(string $payload): string
{
    $frameHead = [];
    $payloadLength = strlen($payload);

    /**
     * FIN = 1 / opcode = 0x1（text frame）
     */
    $frameHead[0] = 0x81;

    if ($payloadLength <= 125) {
        $frameHead[1] = $payloadLength;
    } elseif ($payloadLength <= 65535) {
        $frameHead[1] = 126;
        $frameHead[2] = ($payloadLength >> 8) & 0xff;
        $frameHead[3] = $payloadLength & 0xff;
    } else {
        $frameHead[1] = 127;
        for ($i = 7; $i >= 0; $i--) {
            $frameHead[$i + 2] = ($payloadLength >> ($i * 8)) & 0xff;
        }
    }

    $frame = '';
    foreach ($frameHead as $byte) {
        $frame .= chr($byte);
    }

    return $frame . $payload;
}
