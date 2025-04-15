<?php
require 'db.php';
require 'jwt_helper.php';  // Для работы с JWT

// Если форма отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Проверка, не существует ли уже пользователь с таким именем
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        $error = "Пользователь с таким именем уже существует!";
    } else {
        // Хеширование пароля
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Добавление нового пользователя в базу данных
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashed_password]);

        $success = "Регистрация прошла успешно! Теперь вы можете войти.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1>Регистрация</h1>
    <form method="POST" action="register.php">
        <label for="username">Имя пользователя</label>
        <input type="text" name="username" id="username" required><br>

        <label for="password">Пароль</label>
        <input type="password" name="password" id="password" required><br>

        <button type="submit">Зарегистрироваться</button>
    </form>
    <a href='login1.php'>Войти</a>

    <?php if (isset($error)): ?>
        <p style="color:red"><?= $error ?></p>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <p style="color:green"><?= $success ?></p>
    <?php endif; ?>
</div>
</body>
</html>
