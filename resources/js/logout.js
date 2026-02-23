(() => {
    "use strict";

    const logoutBtn = document.getElementById("logoutBtn");
    if (!logoutBtn) return;

    logoutBtn.addEventListener("click", () => {
        // WebSocket が存在すれば切断
        if (window.ws && window.ws.readyState === WebSocket.OPEN) {
            window.ws.close();
        }

        // ログアウト処理
        location.href = "/logout";
    });
})();
