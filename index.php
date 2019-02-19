<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set("Europe/Moscow");
setlocale(LC_ALL, 'ru_RU');

require_once('./functions.php');

// Показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

// Текущий юзер
$user_id = 3;

// Подключаемся к бд
$connect = mysqli_connect('localhost', 'root', 'vernazza110916', 'doingsdone');
mysqli_set_charset($connect, 'utf8');

// Делаем запрос на получение списка проектов
$sql_projects = 'SELECT * FROM `projects` WHERE `user_id` = ?';
$sql_tasks = 'SELECT * FROM `tasks` WHERE `user_id` = ?';
$sql_users = 'SELECT * FROM `users` WHERE `id` = ?';

// Получаем массив проектов
$projects = get_data($connect, $sql_projects, $user_id);
// Получаем массив задач
$tasks = get_data($connect, $sql_tasks, $user_id);
// Получаем имя текущего пользователя
$users = get_data($connect, $sql_users, $user_id);

// Передаём данные в шаблоны
$page_content = include_template('index.php', ['show_complete_tasks' => $show_complete_tasks, 'tasks' => $tasks]);
$layout_content = include_template('layout.php', [
    'projects' => $projects,
    'tasks' => $tasks,
	'content' => $page_content,
	'user' => $users[0]['name'],
	'title' => 'Дела в порядке - Главная страница'
]);

print($layout_content);
