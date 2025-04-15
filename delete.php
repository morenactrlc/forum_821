<?php
require 'db.php';

$type = $_GET['type'];
$id = $_GET['id'];

// Проверяем тип и выполняем удаление
if ($type === 'reply') {
    $pdo->prepare("DELETE FROM replies WHERE id = ?")->execute([$id]);
} elseif ($type === 'topic') {
    $pdo->prepare("DELETE FROM topics WHERE id = ?")->execute([$id]);
}

// После удаления перенаправляем обратно на главную страницу
header("Location: index.php");
exit;
?>
