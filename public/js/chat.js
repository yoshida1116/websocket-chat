const form = document.getElementById("form");
const message = document.getElementById("message");
const showMessages = document.getElementById("show-message");

const socket = io();

let currentUser = null;

async function loadMe() {
  const res = await fetch("/api/me");
  if (!res.ok) {
    location.href = "/login.html";
    return;
  }
  const me = await res.json();
  currentUser = me.user;
}

function renderMessage(data) {
  const row = document.createElement("div");
  const content = document.createElement("div");
  const nameEl = document.createElement("div");
  const bubble = document.createElement("div");
  const time = document.createElement("div");

  const isSelf = currentUser && data.name === currentUser;

  row.classList.add("message-row", isSelf ? "self" : "other");
  content.style.display = "flex";
  content.style.flexDirection = "column";

  nameEl.classList.add("name");
  nameEl.textContent = data.name;

  bubble.classList.add("bubble");
  bubble.textContent = data.msg;

  const date = new Date(data.time);
  time.classList.add("time");
  time.textContent = date.toLocaleTimeString("ja-JP", {
    hour: "2-digit",
    minute: "2-digit",
  });

  content.appendChild(nameEl);
  content.appendChild(bubble);

  if (isSelf) {
    row.appendChild(time);
    row.appendChild(content);
  } else {
    row.appendChild(content);
    row.appendChild(time);
  }

  showMessages.appendChild(row);
  showMessages.scrollTop = showMessages.scrollHeight;
}

async function loadHistory() {
  const res = await fetch("/api/messages");
  if (!res.ok) return;

  const messages = await res.json();
  messages.forEach((m) => {
    renderMessage({
      name: m.user,
      msg: m.message,
      time: m.created_at,
    });
  });
}

form.addEventListener("submit", (e) => {
  e.preventDefault();

  const msg = message.value;
  if (!msg.trim()) return;

  socket.emit("msgPost", { msg });
  message.value = "";
});

socket.on("msgGet", (data) => {
  renderMessage(data);
});

(async () => {
  await loadMe();
  await loadHistory();
})();

window.addEventListener("beforeunload", () => {
  navigator.sendBeacon("/logout");
});

// ログアウト処理
const logoutBtn = document.getElementById("logoutBtn");

logoutBtn.addEventListener("click", async () => {
  await fetch("/logout", { method: "POST" });
  location.href = "/login.html";
});