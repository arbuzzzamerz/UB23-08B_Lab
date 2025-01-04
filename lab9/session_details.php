<?php
session_start();
require('db_connection.php');

// Проверка, был ли передан идентификатор сеанса в запросе
if (!isset($_GET['session_id'])) {
    header("Location: index.php"); // Перенаправляем на главную страницу, если не указан идентификатор сеанса
    exit();
}

$session_id = $_GET['session_id'];

// Получение подробной информации о сеансе
$session_details_query = "SELECT Sessions.id, Movies.title AS movie_title, Halls.name AS hall_name, Sessions.start_time, Sessions.price, SessionsHalls.available_seats
                          FROM Sessions
                          INNER JOIN Movies ON Sessions.movie_id = Movies.id
                          INNER JOIN SessionsHalls ON Sessions.id = SessionsHalls.session_id
                          INNER JOIN Halls ON SessionsHalls.hall_id = Halls.id
                          WHERE Sessions.id = '$session_id'";
$session_details_result = $conn->query($session_details_query);

if (!$session_details_result || $session_details_result->num_rows == 0) {
    die("Ошибка при получении информации о сеансе");
}

$session_details = $session_details_result->fetch_assoc();

// Проверка, был ли пользователь аутентифицирован
if (!isset($_SESSION['user_id'])) {
    // Перенаправление пользователя на страницу входа или другую страницу по вашему выбору
    header("Location: login.php");
    exit();
}

// Получение user_id из сессии
$user_id = $_SESSION['user_id'];

// Обработка формы бронирования
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reserve_tickets'])) {
    $seats_to_reserve = $_POST['seats_to_reserve'];

    // Проверка доступных мест
    if ($seats_to_reserve <= $session_details['available_seats']) {
        // Создание бронирования
        $reserve_query = "INSERT INTO Bookings (user_id, session_id, seats, created_at) 
                          VALUES ('$user_id', '$session_id', '$seats_to_reserve', NOW())";
        $reserve_result = $conn->query($reserve_query);

        if (!$reserve_result) {
            die("Ошибка при бронировании билетов: " . $conn->error);
        }

        // Уменьшение количества доступных мест
        $update_seats_query = "UPDATE SessionsHalls SET available_seats = available_seats - '$seats_to_reserve' WHERE session_id = '$session_id'";
        $update_seats_result = $conn->query($update_seats_query);

        if (!$update_seats_result) {
            die("Ошибка при обновлении количества мест: " . $conn->error);
        }

        // Перенаправление пользователя после успешного бронирования
        header("Location: session_details.php?session_id=$session_id");
        exit();
    } else {
        $error_message = "Недостаточно свободных мест для бронирования.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подробная информация о сеансе</title>
    <style>
        /* Ваши стили */
    </style>
</head>
<body>
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

    .session-details-container {
        background-color: #212121;
        padding: 20px;
        border-radius: 8px;
        width: 80%;
        max-width: 600px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        box-sizing: border-box;
        margin: auto;
        margin-top: 20px; /* Добавлено для отступа от верхней части страницы */
    }

    h2 {
        text-align: center;
        color: #8a2be2;
        margin-bottom: 20px;
    }

    p {
        color: #fff;
        margin-bottom: 10px;
    }

    form {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    label {
        display: block;
        margin-bottom: 8px;
        color: #8a2be2;
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

    p.error-message {
        color: red;
        margin-top: 10px;
    }
</style>
<div class="session-details-container">
    <h2>Подробная информация о сеансе</h2>

    <p>Фильм: <?php echo $session_details['movie_title']; ?></p>
    <p>Зал: <?php echo $session_details['hall_name']; ?></p>
    <p>Время начала: <?php echo $session_details['start_time']; ?></p>
    <p>Цена билета: <?php echo $session_details['price']; ?></p>
    <p>Доступные места: <?php echo $session_details['available_seats']; ?></p>

    <!-- Форма бронирования -->
    <form action="session_details.php?session_id=<?php echo $session_id; ?>" method="post">
        <label for="seats_to_reserve">Количество билетов:</label>
        <input type="number" name="seats_to_reserve" min="1" max="<?php echo $session_details['available_seats']; ?>" required>
        <button type="submit" name="reserve_tickets">Забронировать</button>
    </form>

    <?php if (isset($error_message)): ?>
        <p style="color: red;"><?php echo $error_message; ?></p>
    <?php endif; ?>
</div>
</body>
</html>
