-- Создание базы данных
CREATE DATABASE IF NOT EXISTS cinema_db;
USE cinema_db;

-- Создание таблицы фильмов
CREATE TABLE IF NOT EXISTS Movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    release_date DATE
);

-- Создание таблицы сеансов
CREATE TABLE IF NOT EXISTS Sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT,
    start_time DATETIME,
    price DECIMAL(8, 2),
    FOREIGN KEY (movie_id) REFERENCES Movies(id)
);

-- Создание таблицы ролей
CREATE TABLE IF NOT EXISTS Roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

-- Вставка ролей "admin" и "user"
INSERT INTO Roles (name) VALUES ('admin'), ('user');

-- Создание таблицы пользователей
CREATE TABLE IF NOT EXISTS Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL
);

-- Создание таблицы ролей пользователей
CREATE TABLE IF NOT EXISTS UserRoles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    role_id INT,
    FOREIGN KEY (user_id) REFERENCES Users(id),
    FOREIGN KEY (role_id) REFERENCES Roles(id)
);

-- Создание таблицы бронирований
CREATE TABLE IF NOT EXISTS Bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id INT,
    seats TEXT,
    created_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES Users(id),
    FOREIGN KEY (session_id) REFERENCES Sessions(id)
);

-- Создание таблицы отзывов
CREATE TABLE IF NOT EXISTS Reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    movie_id INT,
    rating INT,
    comment TEXT,
    created_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES Users(id),
    FOREIGN KEY (movie_id) REFERENCES Movies(id)
);

-- Создание таблицы залов
CREATE TABLE IF NOT EXISTS Halls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    capacity INT NOT NULL
);

-- Вставка залов
INSERT INTO Halls (name, capacity) VALUES ('Малый зал', 50), ('Большой зал', 100), ('VIP зал', 150);

-- Создание таблицы связи между сеансами и залами
CREATE TABLE IF NOT EXISTS SessionsHalls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT,
    hall_id INT,
    available_seats INT,
    FOREIGN KEY (session_id) REFERENCES Sessions(id),
    FOREIGN KEY (hall_id) REFERENCES Halls(id)
);

-- Создание триггера для обновления available_seats
DELIMITER //
CREATE TRIGGER update_available_seats
BEFORE INSERT ON SessionsHalls
FOR EACH ROW
BEGIN
    DECLARE hall_capacity INT;
    SELECT capacity INTO hall_capacity FROM Halls WHERE id = NEW.hall_id;
    SET NEW.available_seats = hall_capacity;
END;
//
DELIMITER ;
