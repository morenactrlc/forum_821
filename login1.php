<?php
require 'db.php';
require 'jwt_helper.php';  // Для работы с JWT

// Если форма отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Проверка на существование пользователя в базе
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Если пользователь найден и пароль совпадает
    if ($user && password_verify($password, $user['password'])) {
        // Генерация JWT
        $payload = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'], // 👈 добавляем роль
            'exp' => time() + 3600
        ];
        
        $jwt = generate_jwt($payload);

        // Сохранение JWT в cookie (или в LocalStorage на клиенте)
        setcookie('token', $jwt, time() + 3600, '/');  // Токен будет храниться 1 час

        // Перенаправление на главную страницу
        header('Location: index.php');
        exit;
    } else {
        $error = "Неверное имя пользователя или пароль!";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1>Вход</h1>
    <form method="POST" action="login1.php">
        <label for="username">Имя пользователя</label>
        <input type="text" name="username" id="username" required><br>

        <label for="password">Пароль</label>
        <input type="password" name="password" id="password" required><br>

        <button type="submit">Войти</button>
    </form>
    <a href='register.php'>Зарегистрироваться</a>
    
    <?php if (isset($error)): ?>
        <p style="color:red"><?= $error ?></p>
    <?php endif; ?>
</div>
</body>
</html>
