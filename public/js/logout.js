(() => {
    "use strict";

    const logoutBtn = document.getElementById("logoutBtn");
    if (!logoutBtn) return;

    logoutBtn.addEventListener("click", () => {
        // WebSocket が存在すれば切断
        if (window.ws && window.ws.readyState === WebSocket.OPEN) {
            window.ws.close();
        }

        // セッション破棄（サーバー側）
        location.href = "/logout.php";
    });
})();
