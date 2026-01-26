(() => {
    "use strict";

    // ===== 設定 =====
    const WS_URL = "ws://localhost:8080"; // Docker で公開している Node.js WebSocket
    const messageList = document.getElementById("show-message");
    const form = document.getElementById("form");
    const input = document.getElementById("message");

    // PHP から注入される前提
    const CURRENT_USER_ID = window.USER_ID;
    const CURRENT_USER_NAME = window.USER_NAME;

    let ws;
    const renderedMessageIds = new Set();

    // ===== WebSocket 接続 =====
    function connect() {
        ws = new WebSocket(WS_URL);

        ws.onopen = () => {
            console.log("WebSocket connected");
        };

        ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            appendMessage(data);
        };

        ws.onclose = () => {
            console.log("WebSocket closed. retry...");
            setTimeout(connect, 3000);
        };

        ws.onerror = () => {
            ws.close();
        };
    }

    // ===== メッセージ送信 =====
    form.addEventListener("submit", (e) => {
        e.preventDefault();

        if (!ws || ws.readyState !== WebSocket.OPEN) {
            console.warn("WebSocket not connected");
            return;
        }

        const text = input.value.trim();
        if (text === "") return;

        const payload = {
            user_id: CURRENT_USER_ID,
            username: CURRENT_USER_NAME,
            message: text,
            sent_at: formatDateTime(new Date())
        };

        ws.send(JSON.stringify(payload));
        input.value = "";
    });

    // ===== メッセージ表示 =====
    function appendMessage(msg) {
        if (msg.id && renderedMessageIds.has(msg.id)) return;
        if (msg.id) renderedMessageIds.add(msg.id);

        const isSelf = msg.user_id === CURRENT_USER_ID;

        const li = document.createElement("li");
        li.className = `message-row ${isSelf ? "self" : "other"}`;

        const body = document.createElement("div");

        const name = document.createElement("div");
        name.className = "name";
        name.textContent = msg.username;

        const bubble = document.createElement("div");
        bubble.className = "bubble";
        bubble.textContent = msg.message;

        body.appendChild(name);
        body.appendChild(bubble);

        const time = createTime(msg.sent_at);

        if (isSelf) {
            li.appendChild(time);
            li.appendChild(body);
        } else {
            li.appendChild(body);
            li.appendChild(time);
        }

        messageList.appendChild(li);
        scrollBottom();
    }

    // ===== 時刻表示 =====
    function createTime(datetime) {
        const div = document.createElement("div");
        div.className = "time";
        const d = new Date(datetime.replace(" ", "T"));
        div.textContent = d.toLocaleTimeString("ja-JP", {
            hour: "2-digit",
            minute: "2-digit"
        });
        return div;
    }

    // ===== ユーティリティ =====
    function scrollBottom() {
        messageList.scrollTop = messageList.scrollHeight;
    }

    function formatDateTime(date) {
        const pad = (n) => String(n).padStart(2, "0");
        return (
            date.getFullYear() + "-" +
            pad(date.getMonth() + 1) + "-" +
            pad(date.getDate()) + " " +
            pad(date.getHours()) + ":" +
            pad(date.getMinutes()) + ":" +
            pad(date.getSeconds())
        );
    }

    // ===== 初期化 =====
    async function loadInitialMessages() {
        try {
            const res = await fetch('/messages'); // Laravel MVC 既存 Controller で JSON を返す
            const messages = await res.json();
            messages.forEach(appendMessage);
        } catch (err) {
            console.error("Failed to load initial messages:", err);
        }
    }

    loadInitialMessages();
    connect();
})();
