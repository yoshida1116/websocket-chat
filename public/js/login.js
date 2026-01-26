document.getElementById("loginForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const userId = document.getElementById("userId").value;
    const password = document.getElementById("password").value;

    const res = await fetch("/login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ userId, password }),
    });

    if (res.ok) {
        location.href = "/";
    } else {
        alert("ログイン失敗");
    }
});