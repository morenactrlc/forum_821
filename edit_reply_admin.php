<?php
require 'db.php';
require 'jwt_helper.php';

$decoded = null;
if (isset($_COOKIE['token'])) {
    $decoded = validate_jwt($_COOKIE['token']);
}
if (!$decoded || $decoded['role'] !== 'admin') {
    die("Доступ запрещен.");
}

$reply_id = (int)$_GET['id'];

// Обновление
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $stmt = $pdo->prepare("UPDATE replies SET content = ? WHERE id = ?");
    $stmt->execute([$_POST['content'], $reply_id]);
    header("Location: admin_replies.php");
    exit;
}

// Получение текущего текста
$stmt = $pdo->prepare("SELECT * FROM replies WHERE id = ?");
$stmt->execute([$reply_id]);
$reply = $stmt->fetch();

if (!$reply) {
    die("Ответ не найден.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Редактировать ответ</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Редактировать ответ</h1>
    <form method="POST">
        <textarea name="content" rows="5" cols="60"><?= htmlspecialchars($reply['content']) ?></textarea><br>
        <button type="submit">Сохранить</button>
    </form>
    <a href="admin_replies.php">Назад</a>
</body>
</html>
