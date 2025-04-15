<?php 
require 'db.php'; 
require 'jwt_helper.php';

// Инициализируем переменные
$decoded = null;
$user = null;

// Проверяем JWT из cookie
if (isset($_COOKIE['token'])) {
    $decoded = validate_jwt($_COOKIE['token']);
    if ($decoded) {
        $user = $decoded;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Форум</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Хедер с меню -->
    <header>
        <div class="menu">
            <a href="index.php">Главная</a>
            <a href="tags.php">Тэги</a>
            <?php if ($user): ?>
                <a href="logout.php">Выйти</a>
            <?php else: ?>
                <a href="login1.php">Войти</a>
            <?php endif; ?>

            <?php if ($decoded && isset($decoded['role']) && $decoded['role'] === 'admin'): ?>
                <a href="admin_replies.php">Админка</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="container">
        <!-- Приветствие -->
        <?php if ($user): ?>
            <p>Привет, <?= htmlspecialchars($user['username']) ?>!</p>
        <?php endif; ?>

        <!-- Форма поиска по автору -->
        <form method="GET" style="margin-top:10px;">
            <input type="text" name="author" placeholder="Поиск по автору">
            <button type="submit">Найти</button>
        </form>

        <h1>Темы</h1>
        <a href="create_topic.php">Создать тему</a>

        <ul>
        <?php
        $filter = "";
        $params = [];

        // Фильтр по тегу
        if (isset($_GET['tag'])) {
            $tag = $_GET['tag'];
            $filter = "WHERE t.id IN (
                SELECT topic_id FROM topic_tags
                JOIN tags ON tags.id = topic_tags.tag_id
                WHERE tags.name = ?
            )";
            $params[] = $tag;
        }

        // Фильтр по автору
        if (isset($_GET['author']) && trim($_GET['author']) !== '') {
            $filter = "WHERE u.username LIKE ?";
            $params[] = '%' . $_GET['author'] . '%';
        }

        // Запрос для получения топиков с фильтрацией
        $sql = "SELECT t.*, u.username FROM topics t LEFT JOIN users u ON t.user_id = u.id $filter ORDER BY t.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Отображение топиков
        while ($row = $stmt->fetch()) {
            echo "<li>";
            echo "<a href='topic.php?id={$row['id']}'>" . htmlspecialchars($row['title']) . "</a>";
            echo " <small>от " . htmlspecialchars($row['username'] ?? 'Неизвестно') . "</small><br>";

            // Выводим теги для каждого топика
            $tagsStmt = $pdo->prepare("
                SELECT name FROM tags 
                JOIN topic_tags ON tags.id = topic_tags.tag_id 
                WHERE topic_tags.topic_id = ?
            ");
            $tagsStmt->execute([$row['id']]);
            $tags = $tagsStmt->fetchAll(PDO::FETCH_COLUMN);

            if ($tags) {
                echo "<small>Тэги: ";
                foreach ($tags as $tag) {
                    echo "<a href='?tag=" . urlencode($tag) . "'>#" . htmlspecialchars($tag) . "</a> ";
                }
                echo "</small>";
            }

            echo "</li>";
        }
        ?>
        </ul>
    </div>
</body>
</html>
