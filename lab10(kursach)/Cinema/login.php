<?php
session_start();
require('db_connection.php'); // Подключение к базе данных
if (isset($_SESSION['email'])) {
    header("Location: profile.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Поиск пользователя в базе данных по email
    $query = "SELECT id, password FROM Users WHERE email='$email'";
    $result = $conn->query($query);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Проверка хэшированного пароля
        if (password_verify($password, $user['password'])) {
            // Пользователь найден, устанавливаем сессию и перенаправляем
            $_SESSION['user_id'] = $user['id']; // Сохраняем id пользователя в сессии
            $_SESSION['email'] = $email;

            header("Location: profile.php");
            exit();
        } else {
            $error_message = "Неправильный email или пароль";
        }
    } else {
        $error_message = "Неправильный email или пароль";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
<h2>Login</h2>
<?php if (isset($error_message)) echo "<p style='color:red;'>$error_message</p>"; ?>
<form action="login.php" method="post">
    <label for="email">Email:</label>
    <input type="email" name="email" required><br>
    <label for="password">Password:</label>
    <input type="password" name="password" required><br>
    <button type="submit">Login</button>
</form>
<p class="question">У вас нет аккаунта? <a href="registration.php"> Зарегистрируйтесь</a></p>
</body>
</html>
