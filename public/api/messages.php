<?php
require __DIR__ . '/../../bootstrap.php';

$pdo = require __DIR__ . '/../../app/config/database.php';
require __DIR__ . '/../../app/model/Message.php';

$messageModel = new Message($pdo);

$messages = $messageModel->fetchLatest(50);

header('Content-Type: application/json');
echo json_encode($messages);
