import express from "express";
import path from "path";
import { createServer } from "http";
import { Server } from "socket.io";

const app = express();
const httpServer = createServer(app);
const _dirname = path.resolve();
const io = new Server(httpServer);

app.get("/", (req, res) => {
    res.sendFile(_dirname + "/index.html");
});

io.on("connection", (socket) => {
    socket.on("msgPost", (data) => {
        const msgs = {
            name: data.name,
            msg: data.msg,
        };
        io.emit("msgGet", msgs);
    });
});

httpServer.listen(3000, () => {
    console.log("listening on port:3000");
});