const WebSocket = require('ws');
const http = require('http');

// WebSocket サーバー起動
const wss = new WebSocket.Server({ port: 8080 });
const clients = new Set();

// Laravel API にメッセージを保存する関数
const save = (data, ws) => {
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

            console.log('STATUS:', res.statusCode);
            console.log('RESPONSE:', resBody);

            if (res.statusCode >= 400) {
                ws.send(resBody);   // ← Laravelのエラーをそのまま返す
                return;
            }

            // 成功時のみ配信
            clients.forEach(c => {
                if (c.readyState === WebSocket.OPEN) {
                    c.send(resBody);
                }
            });
        });
    });

    req.on('error', () => {
        ws.send(JSON.stringify({
            error: "送信に失敗しました。"
        }));
    });

    req.write(body);
    req.end();
};

// WebSocket 接続時の処理
wss.on('connection', ws => {
    clients.add(ws);

    ws.on('message', m => {
        try {
            const msg = JSON.parse(m);

            // Laravel API に保存
            save({
                user_id: msg.user_id,
                message: msg.message,
                sent_at: msg.sent_at,
                received_at: new Date().toISOString().slice(0, 19).replace('T', ' ')
            }, ws);

        } catch {
            ws.send(JSON.stringify({
                error: "送信に失敗しました。"
            }));
        }
    });

    ws.on('close', () => {
        clients.delete(ws);
    });
});

console.log('WebSocket server running on ws://localhost:8080');
