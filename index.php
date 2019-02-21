<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set("Europe/Moscow");
setlocale(LC_ALL, 'ru_RU');

require_once('./functions.php');
require_once('./init.php');

// Показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

// Текущий юзер
$user_id = 3;

// Получаем массив проектов
$projects = get_data($connect, make_projects_request(), $user_id, true);

// Получаем массив задач
$tasks = get_data($connect, make_tasks_request(), $user_id, true);

// Получаем имя текущего пользователя
$users = get_data($connect, make_users_request(), $user_id, false);

// Получаем массив с количеством невыполненных заданий
$tasks_quantity = get_data($connect, make_tasks_quantity_request(), $user_id, true);

// Передаём данные в шаблоны
$page_content = include_template('index.php', ['show_complete_tasks' => $show_complete_tasks, 'tasks' => $tasks]);
$layout_content = include_template('layout.php', [
    'projects' => $projects,
    'tasks' => $tasks,
    'tasks_quantity' => $tasks_quantity,
	'content' => $page_content,
	'user' => $users[0],
	'title' => 'Дела в порядке - Главная страница'
]);

print($layout_content);
