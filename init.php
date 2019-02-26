<?php
// Подключаемся к бд
$connect = mysqli_connect('localhost', 'root', 'vernazza110916', 'doingsdone');
mysqli_set_charset($connect, 'utf8');

session_start();

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
}

$user_id = $user['id'] ?? '';
