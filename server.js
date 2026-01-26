import express from "express";
import { createServer } from "http";
import { Server } from "socket.io";
import session from "express-session";
import sqlite3 from "sqlite3";

const app = express();
const httpServer = createServer(app);
const io = new Server(httpServer);

const USERS = {
    admin: "password123",
    user1: "password123",
    user2: "password123",
    user3: "password123",
};

const db = new sqlite3.Database("./chat.db");

// DB初期化
db.serialize(() => {
  db.run(`
    CREATE TABLE IF NOT EXISTS messages (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      user TEXT NOT NULL,
      message TEXT NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
  `);
});

// JSON
app.use(express.json());

// セッション
const sessionMiddleware = session({
  secret: "chat-secret",
  resave: false,
  saveUninitialized: false,
});
app.use(sessionMiddleware);

// Socket.IO にセッションを共有（重要）
io.engine.use(sessionMiddleware);

// ログインAPI
app.post("/login", (req, res) => {
  const { userId, password } = req.body ?? {};
  if (USERS[userId] === password) {
    req.session.user = userId;
    return res.sendStatus(200);
  }
  return res.sendStatus(401);
});

// ログアウトAPI
app.post("/logout", (req, res) => {
  req.session.destroy((err) => {
    if (err) {
      return res.sendStatus(500);
    }
    res.clearCookie("connect.sid");
    return res.sendStatus(200);
  });
});

// 未ログインでも許可するパス（静的アセット含む）
const allowWhenLoggedOut = (path) => {
    if (path === "/login.html") return true;
    if (path === "/login") return true;
    if (path === "/logout") return true;
    if (path === "/api/me") return true;
    if (path.startsWith("/css/")) return true;
    if (path.startsWith("/js/")) return true;
    if (path.startsWith("/socket.io/")) return true;
    if (path === "/favicon.ico") return true;
    return false;
};

// ログインガード（HTTP）
app.use((req, res, next) => {
  if (req.session?.user) return next();
  if (allowWhenLoggedOut(req.path)) return next();
  return res.redirect("/login.html");
});

// 静的公開
app.use(express.static("public"));

// 自分のログインユーザー取得（chat.js が使用）
app.get("/api/me", (req, res) => {
  if (!req.session?.user) return res.sendStatus(401);
  return res.json({ user: req.session.user });
});

// 履歴取得API（最新100件）
app.get("/api/messages", (req, res) => {
  db.all(
    `
    SELECT user, message, created_at
    FROM (
      SELECT user, message, created_at, id
      FROM messages
      ORDER BY id DESC
      LIMIT 100
    )
    ORDER BY id ASC
    `,
    (err, rows) => {
      if (err) return res.status(500).json({ error: "db error" });
      return res.json(rows);
    }
  );
});

// Socket.IO 認証（セッション必須）
io.use((socket, next) => {
  const sess = socket.request.session;
  if (!sess?.user) return next(new Error("unauthorized"));
  next();
});

io.on("connection", (socket) => {
  socket.on("msgPost", (data) => {
    const user = socket.request.session.user;
    const msg = (data?.msg ?? "").toString();

    if (!msg.trim()) return;

    db.run(
      "INSERT INTO messages (user, message) VALUES (?, ?)",
      [user, msg],
      (err) => {
        if (err) return;

        io.emit("msgGet", {
          name: user,
          msg,
          time: Date.now(),
        });
      }
    );
  });
});

httpServer.listen(3000, () => {
  console.log("listening on port:3000");
});