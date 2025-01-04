<?php
session_start();
require('db_connection.php'); // Подключение к базе данных
if (isset($_SESSION['email'])) {
    header("Location: profile.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_POST['email'];

    // Проверка на уникальность email
    $check_email_query = "SELECT * FROM Users WHERE email='$email'";
    $email_result = $conn->query($check_email_query);

    if ($email_result->num_rows > 0) {
        $_SESSION['error_message'] = "Пользователь с этим email уже зарегистрирован";
        header("Location: registration.php"); // Перенаправляем обратно на страницу регистрации
        exit();
    } elseif ($password != $confirm_password) {
        $_SESSION['error_message'] = "Пароли не совпадают";
        header("Location: registration.php"); // Перенаправляем обратно на страницу регистрации
        exit();
    } elseif (strlen($password) < 6) {
        $_SESSION['error_message'] = "Пароль должен содержать не менее 6 символов";
        header("Location: registration.php"); // Перенаправляем обратно на страницу регистрации
        exit();
    } else {
        // Хэширование пароля
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Регистрация нового пользователя с хэшированным паролем
        $register_query = "INSERT INTO Users (username, password, email) VALUES ('$username', '$hashed_password', '$email')";
        $conn->query($register_query);

        // Получение id нового пользователя
        $user_id = $conn->insert_id;

        // Назначение роли "user" новому пользователю
        $user_role_query = "INSERT INTO UserRoles (user_id, role_id) VALUES ('$user_id', 2)"; // 2 - id роли "user"
        $conn->query($user_role_query);

        header("Location: login.php"); // После регистрации перенаправляем на страницу входа
        exit();
    }
}

// Проверяем наличие ошибки в сессии и удаляем ее
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <style>
        body {
            background-color: #121212;
            color: #fff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            width: 300px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        label {
            display: block;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 8px;
            margin-bottom: 16px;
            box-sizing: border-box;
        }

        button {
            background-color: #8a2be2;
            color: #fff;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        button:hover {
            background-color: #6a1b9a;
        }

        p {
            color: #ff5555;
            margin-top: 10px;
        }
        .question{
            color: white;
        }
        a{
            text-decoration: none;
            color: #8a2be2;
        }
    </style>
</head>
<body>
<h2>Registration</h2>
<?php if (isset($error_message)) echo "<p>$error_message</p>"; ?>
<form action="registration.php" method="post" onsubmit="return validatePassword()">
    <label for="username">Username:</label>
    <input type="text" name="username" required>
    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required>
    <label for="confirm_password">Confirm Password:</label>
    <input type="password" name="confirm_password" required>
    <label for="email">Email:</label>
    <input type="email" name="email" required>
    <button type="submit">Register</button>
</form>
<p class="question">Уже есть аккаунт? <a href="login.php"> Вход</a></p>
</body>
</html>
