<?php
require 'db.php';
require 'jwt_helper.php';

// Проверяем JWT из cookie
$user = null;
if (isset($_COOKIE['token'])) {
    $decoded = validate_jwt($_COOKIE['token']);
    if ($decoded) {
        $user = $decoded;
    }
}

// Если пользователя нет, перенаправляем на страницу входа
if (!$user) {
    header('Location: login1.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $tags = explode(',', $_POST['tags']);

    // Вставляем новую тему
    $pdo->prepare("INSERT INTO topics (title, content, user_id) VALUES (?, ?, ?)")
        ->execute([$title, $content, $user['user_id']]);
    $topic_id = $pdo->lastInsertId();

    // Обработка тэгов
    foreach ($tags as $tag) {
        $tag = trim($tag);
        $stmt = $pdo->prepare("INSERT IGNORE INTO tags (name) VALUES (?)");
        $stmt->execute([$tag]);

        $tag_id = $pdo->query("SELECT id FROM tags WHERE name = " . $pdo->quote($tag))->fetchColumn();
        $pdo->prepare("INSERT INTO topic_tags (topic_id, tag_id) VALUES (?, ?)")->execute([$topic_id, $tag_id]);
    }

    // Обработка голосования
    if (!empty($_POST['question']) && !empty($_POST['options'])) {
        $pdo->prepare("INSERT INTO polls (topic_id, question) VALUES (?, ?)")
            ->execute([$topic_id, $_POST['question']]);
        $poll_id = $pdo->lastInsertId();
        foreach (explode("\n", $_POST['options']) as $option) {
            $pdo->prepare("INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)")
                ->execute([$poll_id, trim($option)]);
        }
    }

    // Перенаправление на главную
    header("Location: index.php");
    exit;
}
?>
<head>
    <link rel="stylesheet" href="styles.css">
</head>
<header>
    <div class="menu">
        <a href="index.php">Главная</a>
        <a href="tags.php">Тэги</a>
        <!-- Если пользователь авторизован, выводим "Выйти" в меню -->
        <?php if ($user): ?>
            <a href="logout.php">Выйти</a>
        <?php else: ?>
            <a href="login1.php">Войти</a>
        <?php endif; ?>
    </div>
</header>

<form method="POST">
    <input name="title" placeholder="Заголовок" required><br>
    <textarea name="content" placeholder="Содержание" required></textarea><br>
    <input name="tags" placeholder="Тэги (через запятую)"><br>
    <h4>Голосование (необязательно):</h4>
    <input name="question" placeholder="Вопрос"><br>
    <textarea name="options" placeholder="Варианты (по одному в строке)"></textarea><br>
    <button>Создать</button>
</form>
<a href='index.php'>Назад</a>
