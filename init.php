<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set("Europe/Moscow");
setlocale(LC_ALL, 'ru_RU');

// Подключаемся к бд
$connect = mysqli_connect('localhost', 'root', 'vernazza110916', 'doingsdone');
mysqli_set_charset($connect, 'utf8');

session_start();

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
}

$user_id = $user['id'] ?? '';
