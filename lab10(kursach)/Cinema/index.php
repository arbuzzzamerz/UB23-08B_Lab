<?php
session_start();
require('db_connection.php');

// Получение списка фильмов
$movies_query = "SELECT * FROM Movies";
$movies_result = $conn->query($movies_query);

if (!$movies_result) {
    die("Ошибка при получении списка фильмов: " . $conn->error);
}
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список фильмов</title>
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

        .movies-list {
            list-style: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .movie-item {
            background-color: #333;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            padding: 20px;
            max-width: 300px;
            text-align: center;
        }

        .movie-item a {
            text-decoration: none;
            color: #8a2be2;
            font-weight: bold;
            font-size: 16px;
        }

        .movie-item a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<h2>Список фильмов</h2>
<ul class="movies-list">
    <?php
    while ($movie = $movies_result->fetch_assoc()) :
        ?>
        <li class="movie-item">
            <a href="sessions.php?movie_id=<?php echo $movie['id']; ?>">
                <h3><?php echo $movie['title']; ?></h3>
            </a>
        </li>
    <?php endwhile; ?>
</ul>
</body>
</html>
