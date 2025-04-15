<?php
require 'db.php';
require 'jwt_helper.php';

// Проверка авторизации
$decoded = null;
if (isset($_COOKIE['token'])) {
    $decoded = validate_jwt($_COOKIE['token']);
}

if (!$decoded || $decoded['role'] !== 'admin') {
    die("Доступ запрещен. Только для администраторов.");
}

// Удаление ответа
if (isset($_GET['delete'])) {
    $reply_id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM replies WHERE id = ?");
    $stmt->execute([$reply_id]);
    header("Location: admin_replies.php");
    exit;
}

// Получаем все ответы с темой и автором
$stmt = $pdo->query("
    SELECT r.*, u.username, t.title 
    FROM replies r
    JOIN users u ON r.user_id = u.id
    JOIN topics t ON r.topic_id = t.id
    ORDER BY r.created_at DESC
");
$replies = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Админ: Ответы</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <div class="menu">
        <a href="index.php">Главная</a>
        <a href="tags.php">Тэги</a>
        <a href="logout.php">Выйти</a>
    </div>
</header>

<div class="container">
    <h1>Все ответы (Админ-панель)</h1>

    <?php foreach ($replies as $reply): ?>
        <div class="reply-block" style="border:1px solid #ccc; padding:10px; margin:10px 0;">
            <p><strong>Автор:</strong> <?= htmlspecialchars($reply['username']) ?></p>
            <p><strong>Тема:</strong> <?= htmlspecialchars($reply['title']) ?></p>
            <p><strong>Ответ:</strong><br><?= nl2br(htmlspecialchars($reply['content'])) ?></p>
            <a href="edit_reply_admin.php?id=<?= $reply['id'] ?>">Редактировать</a> |
            <a href="?delete=<?= $reply['id'] ?>" onclick="return confirm('Удалить ответ?')">Удалить</a>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
