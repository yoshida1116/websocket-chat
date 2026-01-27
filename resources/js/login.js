// CSRFトークン取得
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// --- ログイン ---
document.getElementById("loginForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const userId = document.getElementById("userId").value.trim();
    const password = document.getElementById("password").value.trim();

    if (!userId || !password) {
        alert("ユーザーIDとパスワードを入力してください");
        return;
    }

    try {
        const res = await fetch("/login", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({ userId, password }),
            credentials: 'same-origin'
        });

        if (!res.ok) {
            alert("ログイン失敗");
            return;
        }

        const data = await res.json();
        localStorage.setItem("csrfToken", data.csrfToken);

        // indexへ遷移
        location.href = "/";
    } catch (err) {
        console.error(err);
        alert("通信エラーが発生しました");
    }
});

// --- 登録 ---
document.getElementById("registerForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const userId = document.getElementById("registerUserId").value.trim();
    const password = document.getElementById("registerPassword").value.trim();

    if (!userId || !password) {
        alert("ユーザーIDとパスワードを入力してください");
        return;
    }

    try {
        const res = await fetch("/register", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({ userId, password }),
            credentials: 'same-origin'
        });

        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            alert(data.message || "登録失敗");
            return;
        }

        alert("登録完了。ログインしてください。");
        document.getElementById("registerForm").reset();
    } catch (err) {
        console.error(err);
        alert("通信エラーが発生しました");
    }
});
