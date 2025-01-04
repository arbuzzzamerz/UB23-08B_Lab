<?php
session_start();
require('db_connection.php');

// Проверка роли пользователя
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

// Обработка формы добавления фильма
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_movie'])) {
    $movie_title = $_POST['movie_title'];
    $movie_description = $_POST['movie_description'];

    $add_movie_query = "INSERT INTO Movies (title, description) VALUES ('$movie_title', '$movie_description')";
    $add_movie_result = $conn->query($add_movie_query);

    if (!$add_movie_result) {
        die("Ошибка при добавлении фильма: " . $conn->error);
    }

    // После успешного добавления перенаправляем пользователя
    header("Location: admin.php");
    exit();
}

// Получение списка фильмов
$movies_query = "SELECT * FROM Movies";
$movies_result = $conn->query($movies_query);

if (!$movies_result) {
    die("Ошибка при получении списка фильмов: " . $conn->error);
}

// Получение списка залов
$halls_query = "SELECT * FROM Halls";
$halls_result = $conn->query($halls_query);

if (!$halls_result) {
    die("Ошибка при получении списка залов: " . $conn->error);
}

// Обработка формы добавления сеанса
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_session'])) {
    $movie_id = $_POST['movie_id'];
    $hall_id = $_POST['hall_id'];
    $start_time = $_POST['start_time'];
    $price = $_POST['price'];

    // Добавление сеанса
    $add_session_query = "INSERT INTO Sessions (movie_id, start_time, price) VALUES ('$movie_id', '$start_time', '$price')";
    $add_session_result = $conn->query($add_session_query);

    if (!$add_session_result) {
        die("Ошибка при добавлении сеанса: " . $conn->error);
    }

    // Получение id добавленного сеанса
    $session_id = $conn->insert_id;

    // Связь сеанса и зала
    $link_session_hall_query = "INSERT INTO SessionsHalls (session_id, hall_id) VALUES ('$session_id', '$hall_id')";
    $link_session_hall_result = $conn->query($link_session_hall_query);

    if (!$link_session_hall_result) {
        die("Ошибка при создании связи между сеансом и залом: " . $conn->error);
    }

    // После успешного добавления перенаправляем пользователя
    header("Location: admin.php");
    exit();
}

// Получение списка сеансов
$sessions_query = "SELECT * FROM Sessions";
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
    <title>Админка</title>
    <style>
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

        .admin-container {
            background-color: #212121;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            box-sizing: border-box;
            margin: auto;
        }

        h2, h3 {
            text-align: center;
            color: #8a2be2; /* Цвет заголовков */
        }

        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
        }

        input,
        textarea,
        select {
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

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            margin-bottom: 8px;
        }
    </style>
    <script>
        function toggleVisibility(elementId) {
            var element = document.getElementById(elementId);
            if (element.style.display === "none" || element.style.display === "") {
                element.style.display = "block";
            } else {
                element.style.display = "none";
            }
        }
    </script>
</head>
<body>
<div class="admin-container">
    <h2>Администраторская панель</h2>

    <!-- Кнопки для управления видимостью форм и списков -->





    <button onclick="toggleVisibility('addMovieForm')">Добавление фильмов</button>
    <!-- Форма добавления фильма -->
    <form id="addMovieForm" style="display: none;" action="admin.php" method="post">
        <h3>Добавить фильм</h3>
        <label for="movie_title">Название фильма:</label>
        <input type="text" name="movie_title" required>
        <label for="movie_description">Описание:</label>
        <textarea name="movie_description" required></textarea>
        <button type="submit" name="add_movie">Добавить фильм</button>
    </form>

    <button onclick="toggleVisibility('addSessionForm')">Добавление сеансов</button>
    <!-- Форма добавления сеанса -->
    <form id="addSessionForm" style="display: none;" action="admin.php" method="post">
        <h3>Добавить сеанс</h3>
        <label for="movie_id">Выберите фильм:</label>
        <select name="movie_id" required>
            <?php
            // Возвращаем указатель результата в начало
            $movies_result->data_seek(0);

            while ($movie = $movies_result->fetch_assoc()) : ?>
                <option value="<?php echo $movie['id']; ?>"><?php echo $movie['title']; ?></option>
            <?php endwhile; ?>
        </select>
        <label for="hall_id">Выберите зал:</label>
        <select name="hall_id" required>
            <?php
            // Возвращаем указатель результата в начало
            $halls_result->data_seek(0);

            while ($hall = $halls_result->fetch_assoc()) : ?>
                <option value="<?php echo $hall['id']; ?>"><?php echo $hall['name']; ?></option>
            <?php endwhile; ?>
        </select>
        <label for="start_time">Время начала сеанса:</label>
        <input type="datetime-local" name="start_time" required>
        <label for="price">Цена билета:</label>
        <input type="number" name="price" step="1" required>
        <button type="submit" name="add_session">Добавить сеанс</button>
    </form>

    <button onclick="toggleVisibility('moviesList')">Показать список фильмов</button>
    <!-- Список фильмов -->
    <div id="moviesList" style="display: none;">
        <h3>Список фильмов</h3>
        <ul>
            <?php
            $movies_result->data_seek(0);
            while ($movie = $movies_result->fetch_assoc()) : ?>
                <li><?php echo $movie['title']; ?> - <?php echo $movie['description']; ?></li>
            <?php endwhile; ?>
        </ul>
    </div>

    <button onclick="toggleVisibility('sessionsList')">Показать список сеансов</button>
    <!-- Список сеансов -->
    <div id="sessionsList" style="display: none;">
        <h3>Список сеансов</h3>
        <ul>
            <?php
            $sessions_query = "SELECT Sessions.id, Movies.title AS movie_title FROM Sessions
                      INNER JOIN Movies ON Sessions.movie_id = Movies.id";
            $sessions_result = $conn->query($sessions_query);

            if (!$sessions_result) {
                die("Ошибка при получении списка сеансов: " . $conn->error);
            }

            $sessions_result->data_seek(0);
            while ($session = $sessions_result->fetch_assoc()) : ?>
                <li>
                    <a href="session_details.php?session_id=<?php echo $session['id']; ?>">
                        Сеанс #<?php echo $session['id']; ?> - <?php echo $session['movie_title']; ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
    <button name="userListButton" onclick="toggleVisibility('userList')">Показать список пользователей</button>
    <div id="userList" style="display: none;">
        <h3>Список пользователей</h3>
        <ul>
            <?php
            $users_query = "SELECT id, username FROM Users";
            $users_result = $conn->query($users_query);

            if (!$users_result) {
                die("Ошибка при получении списка пользователей: " . $conn->error);
            }

            while ($user = $users_result->fetch_assoc()) : ?>
                <li>
                    <a href="user_details.php?user_id=<?php echo $user['id']; ?>">
                        Пользователь #<?php echo $user['id']; ?> - <?php echo $user['username']; ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</div>
</body>
</html>

