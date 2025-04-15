<?php
require 'db.php';
require 'jwt_helper.php'; // добавляем, чтобы использовать JWT

// Проверяем JWT из cookie
$decoded = null;
if (isset($_COOKIE['token'])) {
    $decoded = validate_jwt($_COOKIE['token']);
}

// Получаем poll_id из URL
$poll_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($poll_id == 0) {
    die("Ошибка: ID опроса не передан.");
}

// Получаем опрос по poll_id
$poll = $pdo->prepare("SELECT * FROM polls WHERE id = ?");
$poll->execute([$poll_id]);
$poll = $poll->fetch();

if (!$poll) {
    die("Опрос с ID $poll_id не найден.");
}

// Получаем все варианты ответа для этого опроса
$stmt = $pdo->prepare("SELECT option_text, votes FROM poll_options WHERE poll_id = ?");
$stmt->execute([$poll_id]);
$options = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Результаты опроса</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <div class="menu">
        <a href="index.php">Главная</a>
        <a href="tags.php">Тэги</a>
        <!-- Если пользователь авторизован, выводим "Выйти" в меню -->
        <?php if ($decoded): ?>
            <a href="logout.php">Выйти</a>
        <?php else: ?>
            <a href="login1.php">Войти</a>
        <?php endif; ?>
    </div>
</header>

<div class="container">
    <h1>Результаты опроса: <?= htmlspecialchars($poll['question']) ?></h1>

    <ul>
        <?php foreach ($options as $option): ?>
            <li><?= htmlspecialchars($option['option_text']) ?> — <?= $option['votes'] ?> голос(ов)</li>
        <?php endforeach; ?>
    </ul>

    <a href="index.php">← Назад к голосованию</a>
</div>
</body>
</html>
