<?php
session_start();
require('db_connection.php');

// Если пользователь не авторизован, перенаправляем на страницу login.php
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Если не были переданы данные формы
if (!isset($_POST['new-username'])) {
    header("Location: profile.php");
    exit();
}

// Получаем новое имя пользователя
$newUsername = $_POST['new-username'];

// Получаем информацию о пользователе
$email = $_SESSION['email'];
$user_query = "SELECT * FROM Users WHERE email='$email'";
$user_result = $conn->query($user_query);

if ($user_result->num_rows == 1) {
    $user = $user_result->fetch_assoc();
    $userId = $user['id'];

    // Обновляем имя пользователя в базе данных
    $updateUsernameQuery = "UPDATE Users SET username='$newUsername' WHERE id='$userId'";
    $conn->query($updateUsernameQuery);

    // Перенаправляем пользователя на страницу profile.php после изменения имени
    header("Location: profile.php");
    exit();
} else {
    // Если что-то пошло не так, перенаправляем на страницу profile.php
    header("Location: profile.php");
    exit();
}
?>
