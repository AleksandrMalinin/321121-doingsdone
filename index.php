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

// Получаем имя текущего пользователя
$users = get_users_data($connect, $user_id);

// Получаем массив с количеством невыполненных заданий
$tasks_quantity = get_tasks_quantity($connect, $user_id);

// Получаем массив проектов
$projects = get_projects_data($connect, $user_id, $tasks_quantity);

// Получаем массив задач
$tasks = get_tasks_data($connect, $user_id, $show_complete_tasks);

// Получаем общее количество задач (Все)
$all_tasks = get_tasks_quantity($connect, $user_id, 'all');

// Получаем количество задач без проектов (Входящие)
$random_tasks = get_tasks_quantity($connect, $user_id, 'incoming');

// Проверяем был ли передан параметр запроса
if (isset($_GET['id']) && $_GET['id'] !== 'all') {
    if ($_GET['id'] === 'incoming') {
        $id = NULL;
    } else {
        $id = intval($_GET['id']);
        $project = is_project($connect, $user_id, $id);
    }

    if ($_GET['id'] === 'incoming' || $project) {
        $tasks = get_tasks_data($connect, $user_id, $show_complete_tasks, $id);
    } else {
        http_response_code(404);
        die();
    }
}

// Передаём массив с задачами в шаблон
$page_content = include_template('index.php', ['show_complete_tasks' => $show_complete_tasks, 'tasks' => $tasks]);

$layout_content = include_template('layout.php', [
    'projects' => $projects,
    'incoming' => $random_tasks['COUNT(*)'],
    'tasks_all' => $all_tasks['COUNT(*)'],
    'tasks_quantity' => $tasks_quantity,
	'content' => $page_content,
	'user' => $users['name'],
	'title' => 'Дела в порядке - Главная страница'
]);

print($layout_content);
