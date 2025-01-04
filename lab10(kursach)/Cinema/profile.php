<?php
session_start();
require('db_connection.php');

// Если пользователь не авторизован, перенаправляем на страницу login.php
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Получаем информацию о пользователе
$email = $_SESSION['email'];
$user_query = "SELECT * FROM Users WHERE email='$email'";
$user_result = $conn->query($user_query);

if ($user_result->num_rows == 1) {
    $user = $user_result->fetch_assoc();
    $user_id = $user['id'];
    $username = $user['username'];

    // Получение роли пользователя
    $role_query = "SELECT roles.name FROM UserRoles INNER JOIN roles ON UserRoles.role_id = roles.id WHERE user_id={$user_id}";
    $role_result = $conn->query($role_query);
    $role = ($role_result->num_rows > 0) ? $role_result->fetch_assoc()['name'] : 'Не определено';

    // Получение броней пользователя
    $bookings_query = "SELECT * FROM Bookings WHERE user_id='$user_id'";
    $bookings_result = $conn->query($bookings_query);

    if (!$bookings_result) {
        die("Ошибка при получении броней пользователя: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>
    <style>
        body {
            background-color: #1c1c1c;
            color: #fff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .profile-container {
            background-color: #333;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            padding: 20px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        h2 {
            color: #8a2be2;
        }

        p {
            margin: 10px 0;
        }

        .logout-btn,
        .cancel-booking-btn {
            background-color: #8a2be2;
            color: #fff;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            border-radius: 5px;
            margin-top: 20px;
        }

        .logout-btn:hover,
        .cancel-booking-btn:hover {
            background-color: #6a1b9a;
        }

        /* Стили для формы изменения имени */
        #change-name-form {
            display: none;
            margin-top: 20px;
        }

        #change-name-btn {
            background-color: #8a2be2;
            color: #fff;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }

        #change-name-btn:hover {
            background-color: #6a1b9a;
        }

        /* Стили для брони пользователя */
        .bookings-container {
            margin-top: 20px;
        }

        .bookings-list {
            list-style: none;
            padding: 0;
        }

        .booking-item {
            margin-bottom: 20px;
            border: 1px solid #555;
            padding: 10px;
            border-radius: 5px;
        }

        .cancel-booking-form {
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="profile-container">
    <h2>Добро пожаловать, <?php echo $username; ?>!</h2>
    <p><strong>Email:</strong> <?php echo $email; ?></p>
    <p><strong>Роль:</strong> <?php echo ($role == 'admin') ? 'Администратор' : 'Пользователь'; ?></p>

    <!-- Отображение броней пользователя -->
    <?php if ($bookings_result->num_rows > 0) : ?>
        <h3>Ваши брони:</h3>
        <ul>
            <?php while ($booking = $bookings_result->fetch_assoc()) : ?>
                <li>
                    Сеанс #<?php echo $booking['session_id']; ?> - <?php echo $booking['seats']; ?> мест(о)
                    <form action="cancel_booking.php" method="post">
                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                        <button type="submit">Отменить бронь</button>
                    </form>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else : ?>
        <p>У вас нет активных броней.</p>
    <?php endif; ?>

    <!-- Кнопка для выхода из аккаунта -->
    <form action="logout.php" method="post">
        <button type="submit" class="logout-btn">Выйти</button>
    </form>
</div>
</body>
</html>
