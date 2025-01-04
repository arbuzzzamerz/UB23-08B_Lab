<?php
session_start();
require('db_connection.php');

// Проверка, является ли пользователь администратором
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$role_query = "SELECT roles.name FROM UserRoles INNER JOIN roles ON UserRoles.role_id = roles.id INNER JOIN Users ON UserRoles.user_id = Users.id WHERE Users.email='$email'";
$role_result = $conn->query($role_query);

if (!$role_result) {
    die("Ошибка при выполнении запроса к базе данных: " . $conn->error);
}

$role = ($role_result->num_rows > 0) ? $role_result->fetch_assoc()['name'] : 'Undefined';

// Проверка, является ли пользователь администратором
if ($role != 'admin') {
    header("Location: index.php"); // Перенаправляем на главную страницу, если не администратор
    exit();
}

// Получение user_id из параметров запроса
if (!isset($_GET['user_id'])) {
    header("Location: admin.php");
    exit();
}

$user_id = $_GET['user_id'];

// Обработка запроса на удаление пользователя
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    // Удаление связанных данных из таблицы userroles
    $delete_userroles_query = "DELETE FROM userroles WHERE user_id = '$user_id'";
    $delete_userroles_result = $conn->query($delete_userroles_query);

    if (!$delete_userroles_result) {
        die("Ошибка при удалении связанных данных из таблицы userroles: " . $conn->error);
    }

    // Удаление связанных данных из таблицы bookings
    $delete_bookings_query = "DELETE FROM bookings WHERE user_id = '$user_id'";
    $delete_bookings_result = $conn->query($delete_bookings_query);

    if (!$delete_bookings_result) {
        die("Ошибка при удалении связанных данных из таблицы bookings: " . $conn->error);
    }

    // Удаление пользователя
    $delete_user_query = "DELETE FROM Users WHERE id = '$user_id'";
    $delete_user_result = $conn->query($delete_user_query);

    if (!$delete_user_result) {
        die("Ошибка при удалении пользователя: " . $conn->error);
    }

    // После успешного удаления перенаправляем пользователя
    header("Location: admin.php");
    exit();
}

// Получение подробной информации о пользователе
$user_details_query = "SELECT * FROM Users WHERE id = '$user_id'";
$user_details_result = $conn->query($user_details_query);

if (!$user_details_result || $user_details_result->num_rows == 0) {
    die("Ошибка при получении информации о пользователе");
}

$user_details = $user_details_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подробная информация о пользователе</title>
    <style>
        /* Ваши стили */
    </style>
</head>
<body>
<style>
    /* Стили для страницы user_details.php в темной теме */

    body {
        background-color: #121212;
        color: #fff;
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .user-details-container {
        background-color: #212121;
        padding: 20px;
        border-radius: 8px;
        width: 80%;
        max-width: 600px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        box-sizing: border-box;
        margin: auto;
    }

    h2, p {
        color: #8a2be2; /* Цвет заголовков и текста */
        margin-bottom: 10px;
    }

    button {
        background-color: #8a2be2;
        color: #fff;
        padding: 10px 15px;
        border: none;
        cursor: pointer;
        font-size: 16px;
    }

    button:hover {
        background-color: #6a1b9a;
    }
</style>
<div class="user-details-container">
    <h2>Подробная информация о пользователе</h2>

    <p>ID пользователя: <?php echo $user_details['id']; ?></p>
    <p>Email: <?php echo $user_details['email']; ?></p>
    <p>Имя: <?php echo $user_details['username']; ?></p>

    <!-- Кнопка для удаления пользователя -->
    <form action="user_details.php?user_id=<?php echo $user_id; ?>" method="post">
        <button type="submit" name="delete_user">Удалить пользователя</button>
    </form>
</div>
</body>
</html>
