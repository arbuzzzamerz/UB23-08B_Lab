<?php
session_start();

// Проверяем, авторизован ли пользователь
$nav_content = '';

if (isset($_SESSION['email'])) {
    $nav_content .= '<a href="index.php">Главная</a>';

    $email = $_SESSION['email'];
    $nav_content .= '<a href="profile.php">' . $email . '</a>';

    // Проверяем, является ли пользователь администратором
    require('db_connection.php');
    $role_query = "SELECT roles.name FROM UserRoles INNER JOIN roles ON UserRoles.role_id = roles.id WHERE user_id = (SELECT id FROM Users WHERE email = '$email')";
    $role_result = $conn->query($role_query);

    if ($role_result && $role_result->num_rows > 0) {
        $role = $role_result->fetch_assoc()['name'];
        if ($role === 'admin') {
            $nav_content .= '<a href="admin.php">Админка</a>';
        }
    }
} else {
    $nav_content .= '<a href="index.php">Главная</a>';
    $nav_content .= '<a href="login.php">Вход</a>';
    $nav_content .= '<a href="register.php">Регистрация</a>';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        nav {
            background-color: #333;
            padding: 10px;
            text-align: right;
            width: 100%;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
        }
    </style>
</head>
<body>
<header>
    <nav>
        <?php echo $nav_content; ?>
    </nav>
</header>

</body>
</html>

