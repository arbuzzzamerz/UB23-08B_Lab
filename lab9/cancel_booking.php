<?php
session_start();
require('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_id = $_POST['booking_id'];

    // Получение информации о брони
    $booking_query = "SELECT * FROM Bookings WHERE id='$booking_id'";
    $booking_result = $conn->query($booking_query);

    if ($booking_result->num_rows == 1) {
        $booking = $booking_result->fetch_assoc();
        $session_id = $booking['session_id'];
        $seats_to_cancel = $booking['seats'];

        // Отмена брони
        $cancel_booking_query = "DELETE FROM Bookings WHERE id='$booking_id'";
        $cancel_booking_result = $conn->query($cancel_booking_query);

        if (!$cancel_booking_result) {
            die("Ошибка при отмене брони: " . $conn->error);
        }

        // Увеличение количества доступных мест для сеанса
        $update_seats_query = "UPDATE SessionsHalls SET available_seats = available_seats + '$seats_to_cancel' WHERE session_id = '$session_id'";
        $update_seats_result = $conn->query($update_seats_query);

        if (!$update_seats_result) {
            die("Ошибка при обновлении количества мест: " . $conn->error);
        }

        // Перенаправление пользователя после успешной отмены
        header("Location: profile.php");
        exit();
    } else {
        die("Бронь не найдена.");
    }
} else {
    header("Location: profile.php");
    exit();
}
?>

