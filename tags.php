<?php
require 'db.php';
require 'jwt_helper.php'; // добавляем для JWT

// Проверка токена
$decoded = null;
if (isset($_COOKIE['token'])) {
    $decoded = validate_jwt($_COOKIE['token']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Тэги</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Хедер с меню -->
    <header>
        <div class="menu">
            <a href="index.php">Главная</a>
            <a href="tags.php">Тэги</a>
            <?php if ($decoded): ?>
                <a href="logout.php">Выйти</a>
            <?php else: ?>
                <a href="login1.php">Войти</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="container">
        <h1>Тэги</h1>

        <ul>
        <?php
        // Запрос для получения всех тегов
        $stmt = $pdo->query("SELECT * FROM tags");
        while ($row = $stmt->fetch()) {
            echo "<li><a href='index.php?tag=" . urlencode($row['name']) . "'>" . htmlspecialchars($row['name']) . "</a></li>";
        }
        ?>
        </ul>
    </div>
</body>
</html>
