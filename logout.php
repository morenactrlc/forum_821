<?php
// Удаляем cookie с JWT
setcookie('token', '', time() - 3600, '/');
header('Location: index.php');
exit;
