<?php
session_start();

// Уничтожаем все сессии
session_destroy();

// Перенаправляем пользователя на страницу входа
header("Location: login.php");
exit();
?>
