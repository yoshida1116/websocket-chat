document.getElementById("loginForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const userId = document.getElementById("userId").value;
  const password = document.getElementById("password").value;

  const res = await fetch("/api/login.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ userId, password }),
  });

  if (!res.ok) {
    alert("ログイン失敗");
    return;
  }

  const data = await res.json();
  localStorage.setItem("csrfToken", data.csrfToken);

  location.href = "/index.php";
});

document.getElementById("registerForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const userId = document.getElementById("registerUserId").value;
  const password = document.getElementById("registerPassword").value;

  if (!userId || !password) {
    alert("ユーザーIDとパスワードを入力してください");
    return;
  }

  const res = await fetch("/api/register.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ userId, password }),
  });

  if (!res.ok) {
    const text = await res.text();
    alert(text);
    return;
  }

  alert("登録完了。ログインしてください。");
  document.getElementById("registerForm").reset();
});