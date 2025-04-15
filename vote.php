<?php
require 'db.php';

if (!isset($_POST['option_id'])) {
    die("Ошибка: вы не выбрали вариант ответа.");
}

$option_id = (int) $_POST['option_id'];

// Обновляем количество голосов
try {
    $stmt = $pdo->prepare("UPDATE poll_options SET votes = votes + 1 WHERE id = ?");
    $stmt->execute([$option_id]);

    if ($stmt->rowCount() > 0) {
        // Перенаправляем на страницу с результатами
        header("Location: results.php?id=" . $_POST['poll_id']);
        exit;
    } else {
        echo "Ошибка: голос не был учтен. Проверьте ID варианта ответа.";
    }

} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}
?>
