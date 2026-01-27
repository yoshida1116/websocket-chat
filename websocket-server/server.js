const WebSocket = require('ws');
const axios = require('axios');

const wss = new WebSocket.Server({ port: 8080 });
const clients = new Set();

wss.on('connection', ws => {
    clients.add(ws);

    ws.on('message', async data => {
        const msg = JSON.parse(data);

        // 全クライアントに送信
        for (const client of clients) {
            if (client.readyState === WebSocket.OPEN) {
                client.send(JSON.stringify(msg));
            }
        }

        // Laravel に保存
        try {
            await axios.post('http://nginx/messages', {
                user_id: msg.user_id,
                username: msg.username,
                message: msg.message,
                sent_at: msg.sent_at,
                received_at: new Date().toISOString().replace("T", " ").split(".")[0]
            });
        } catch (err) {
            console.error("Laravel 保存失敗:", err.response?.data || err.message);
        }
    });

    ws.on('close', () => clients.delete(ws));
});

console.log("WebSocket server running on ws://localhost:8080");
