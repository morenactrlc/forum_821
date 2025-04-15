<?php
require 'db.php';
require 'jwt_helper.php'; // Подключаем JWT-функции

$topic_id = (int)$_GET['id'];
$topic = $pdo->query("SELECT * FROM topics WHERE id = $topic_id")->fetch();
$poll = $pdo->query("SELECT * FROM polls WHERE topic_id = $topic_id")->fetch();

// Получаем ответы для темы, а также имя пользователя
$stmt = $pdo->prepare("SELECT replies.*, users.username FROM replies JOIN users ON replies.user_id = users.id WHERE topic_id = ?");
$stmt->execute([$topic_id]);
$replies = $stmt->fetchAll();

// Проверка наличия и валидности JWT
$decoded = null;
if (isset($_COOKIE['token'])) {
    // Получаем токен из cookie
    $jwt = $_COOKIE['token'];
    
    // Проверяем и валидируем токен
    $decoded = validate_jwt($jwt);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($topic['title']) ?></title>
    <link rel="stylesheet" href="styles.css">
    <script>
        // Функция для подтверждения удаления
        function confirmDelete() {
            return confirm("Вы уверены, что хотите удалить этот элемент?");
        }

        // Валидация пустых полей перед отправкой формы
        document.addEventListener("DOMContentLoaded", () => {
            const replyForm = document.querySelector('#replyForm');
            if (replyForm) {
                replyForm.addEventListener("submit", function(e) {
                    const textarea = this.querySelector("textarea[name='content']");
                    if (textarea.value.trim() === "") {
                        alert("Комментарий не может быть пустым.");
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
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
    <h1 class="topic-title"><?= htmlspecialchars($topic['title']) ?></h1>
    <p><?= nl2br(htmlspecialchars($topic['content'])) ?></p>

    <?php if ($poll): ?>
        <form method="POST" action="vote.php">
            <input type="hidden" name="poll_id" value="<?= $poll['id'] ?>">
            <p><strong><?= htmlspecialchars($poll['question']) ?></strong></p>
            <?php
            $options = $pdo->query("SELECT * FROM poll_options WHERE poll_id = {$poll['id']}");
            foreach ($options as $opt) {
                echo "<label><input type='radio' name='option_id' value='{$opt['id']}' required> " . htmlspecialchars($opt['option_text']) . "</label><br>";
            }
            ?>
            <button type="submit">Голосовать</button>
        </form>
    <?php endif; ?>

    <h3>Ответы</h3>
    <?php foreach ($replies as $reply): ?>
        <div class="reply">
            <p><strong><?= htmlspecialchars($reply['username']) ?>:</strong></p>
            <p><?= nl2br(htmlspecialchars($reply['content'])) ?></p>

            <?php if ($decoded && $decoded['user_id'] === $reply['user_id']): ?>
                <a href="topic.php?id=<?= $topic_id ?>&edit=<?= $reply['id'] ?>">Редактировать</a> |
                <a href="delete.php?type=reply&id=<?= $reply['id'] ?>" onclick="return confirmDelete()">Удалить</a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <?php if ($decoded): ?>
        <form method="POST" action="topic.php?id=<?= $topic_id ?>" id="replyForm">
            <textarea name="content" required placeholder="Напишите ваш ответ..."></textarea><br>
            <button>Ответить</button>
        </form>
    <?php else: ?>
        <p>Вы должны быть авторизованы, чтобы добавить ответ.</p>
    <?php endif; ?>

    <a href="index.php" class="back-link">← Назад</a>
</div>
</body>
</html>

<?php
// Добавление нового ответа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    if ($decoded) {  // Проверка на авторизацию
        $stmt = $pdo->prepare("INSERT INTO replies (topic_id, content, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$topic_id, $_POST['content'], $decoded['user_id']]);
        header("Location: topic.php?id=$topic_id");
        exit;
    } else {
        echo "Вы не авторизованы для добавления ответа.";
    }
}

// Обновление существующего ответа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_reply'], $_GET['edit'])) {
    $reply_id = (int) $_GET['edit'];
    $updated = trim($_POST['updated_content']);

    if ($decoded) { // Проверка на авторизацию
        $stmt = $pdo->prepare("UPDATE replies SET content = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$updated, $reply_id, $decoded['user_id']]);

        header("Location: topic.php?id=$topic_id");
        exit;
    } else {
        echo "Вы не авторизованы для редактирования этого ответа.";
    }
}
?>
