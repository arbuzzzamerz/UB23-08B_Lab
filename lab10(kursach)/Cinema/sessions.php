<?php
session_start();
require('db_connection.php');

// Проверяем, передан ли идентификатор фильма
if (!isset($_GET['movie_id'])) {
    header("Location: index.php");
    exit();
}

$movie_id = $_GET['movie_id'];

// Получение информации о фильме
$movie_query = "SELECT * FROM Movies WHERE id='$movie_id'";
$movie_result = $conn->query($movie_query);

if (!$movie_result || $movie_result->num_rows != 1) {
    header("Location: index.php");
    exit();
}

$movie = $movie_result->fetch_assoc();

// Получение списка сеансов по выбранному фильму
$sessions_query = "SELECT Sessions.id, Halls.name AS hall_name, Sessions.start_time, Sessions.price, SessionsHalls.available_seats
                  FROM Sessions
                  INNER JOIN SessionsHalls ON Sessions.id = SessionsHalls.session_id
                  INNER JOIN Halls ON SessionsHalls.hall_id = Halls.id
                  WHERE Sessions.movie_id = '$movie_id'";
$sessions_result = $conn->query($sessions_query);

if (!$sessions_result) {
    die("Ошибка при получении списка сеансов: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список сеансов</title>
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
            color: #8a2be2;
        }

        .sessions-list {
            list-style: none;
            padding: 0;
        }

        .session-item {
            background-color: #333;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            padding: 20px;
            max-width: 400px;
            text-align: center;
            margin-bottom: 20px;
        }

        .session-item a {
            text-decoration: none;
            color: #8a2be2;
            font-weight: bold;
            font-size: 16px;
        }

        .session-item a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<h2>Список сеансов для фильма "<?php echo $movie['title']; ?>"</h2>
<ul class="sessions-list">
    <?php
    while ($session = $sessions_result->fetch_assoc()) :
        ?>
        <li class="session-item">
            <a href="session_details.php?session_id=<?php echo $session['id']; ?>">
                <h3>Сеанс #<?php echo $session['id']; ?></h3>
                <p>Зал: <?php echo $session['hall_name']; ?></p>
                <p>Время начала: <?php echo $session['start_time']; ?></p>
                <p>Цена билета: <?php echo $session['price']; ?></p>
                <p>Доступные места: <?php echo $session['available_seats']; ?></p>
            </a>
        </li>
    <?php endwhile; ?>
</ul>
</body>
</html>
