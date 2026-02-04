const WebSocket = require('ws');
const http = require('http');

// WebSocket サーバー起動
const wss = new WebSocket.Server({ port: 8080 });
const clients = new Set();

// Laravel API にメッセージを保存する関数
const save = (data) => {
    const body = JSON.stringify(data);

    const options = {
        hostname: 'laravel_nginx',
        port: 80,
        path: '/api/messages',
        method: 'POST',
        headers: {
            'Content-Type': 'application/json; charset=utf-8',
            'Content-Length': Buffer.byteLength(body)
        }
    };

    const req = http.request(options, res => {
        let resBody = '';
        res.on('data', chunk => { resBody += chunk; });
        res.on('end', () => {
            console.log(`POST status: ${res.statusCode}`, resBody);
        });
    });

    req.on('error', err => {
        console.error('HTTP POST error:', err.message);
    });

    req.write(body); // 本文を送信
    req.end();       // リクエスト終了
};

// WebSocket 接続時の処理
wss.on('connection', ws => {
    clients.add(ws);

    ws.on('message', m => {
        try {
            const msg = JSON.parse(m);

            // 接続中の全クライアントにメッセージを配信
            clients.forEach(c => {
                if (c.readyState === WebSocket.OPEN) c.send(m);
            });

            // Laravel API に保存
            save({
                user_id: String(msg.user_id),
                message: msg.message,
                sent_at: msg.sent_at,
                received_at: new Date().toISOString().slice(0, 19).replace('T', ' ')
            });

        } catch (err) {
            console.error('Failed to process message:', err);
        }
    });

    ws.on('close', () => {
        clients.delete(ws);
    });
});

console.log('WebSocket server running on ws://localhost:8080');
