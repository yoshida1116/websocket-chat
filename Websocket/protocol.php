<?php
declare(strict_types=1);

/**
 * WebSocket プロトコル処理
 * - handshake
 * - frame decode
 * - frame encode
 */

/**
 * WebSocket ハンドシェイク
 */
function websocket_handshake($client, string $request): void
{
    if (!preg_match('/Sec-WebSocket-Key:\s*(.+)\r\n/i', $request, $matches)) {
        throw new RuntimeException('Invalid WebSocket handshake request');
    }

    $key = trim($matches[1]);
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
 * WebSocket フレームをデコード
 * クライアント → サーバー
 */
function websocket_decode(string $data): string
{
    $length = ord($data[1]) & 127;

    if ($length === 126) {
        $mask = substr($data, 4, 4);
        $payload = substr($data, 8);
    } elseif ($length === 127) {
        $mask = substr($data, 10, 4);
        $payload = substr($data, 14);
    } else {
        $mask = substr($data, 2, 4);
        $payload = substr($data, 6);
    }

    $text = '';
    $payloadLength = strlen($payload);

    for ($i = 0; $i < $payloadLength; $i++) {
        $text .= $payload[$i] ^ $mask[$i % 4];
    }

    return $text;
}

/**
 * WebSocket フレームをエンコード
 * サーバー → クライアント
 */
function websocket_encode(string $payload): string
{
    $frameHead = [];
    $payloadLength = strlen($payload);

    $frameHead[0] = 0x81; // FIN + text frame

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
    foreach ($frameHead as $b) {
        $frame .= chr($b);
    }

    return $frame . $payload;
}
