<?php
require 'db.php'; // Подключаем подключение к базе данных
require 'jwt_helper.php'; // Подключаем функции для работы с JWT

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Проверяем, существует ли пользователь в базе данных
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Если пользователь найден и пароль правильный, создаем JWT
        $payload = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + 3600 // Токен истекает через 1 час
        ];

        $jwt = generate_jwt($payload);

        // Отправляем токен в ответ
        echo json_encode(['message' => 'Успешный вход', 'token' => $jwt]);
    } else {
        echo json_encode(['message' => 'Неверный логин или пароль']);
    }
}
?>
