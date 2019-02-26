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
$initial_projects = get_projects_data($connect, $user_id);

// Получаем имя текущего пользователя
$users = get_users_data($connect, $user_id);

// Получаем массив с количеством невыполненных заданий
$tasks_quantity = get_tasks_quantity_data($connect, $user_id);

for ($i = 0; $i < count($initial_projects); $i++) {
    $tasks_count = $tasks_quantity[$i];

    $project = [
        'id' => $initial_projects[$i]['id'],
        'name' => $initial_projects[$i]['name'],
        'tasks_count' => $tasks_count['COUNT(*)'],
        'link' => '/index.php?id=' . $initial_projects[$i]['id']
    ];

    // массив с проектами
    $projects[] = $project;

    // массив с id проектов
    $projects_id[] = $initial_projects[$i]['id'];
}

// проверяем был ли передан параметр запроса
if (isset($_GET['id'])) {
    // проверяем, что id существует
    if (in_array($_GET['id'], $projects_id)) {
        $id = intval($_GET['id']);

        $tasks = get_tasks_data($connect, $user_id, $show_complete_tasks, $id);
    } else {
        http_response_code(404);
        die();
    }
} else {
    $tasks = get_tasks_data($connect, $user_id, $show_complete_tasks);
}

// Передаём массив с задачами в шаблон
$page_content = include_template('index.php', ['show_complete_tasks' => $show_complete_tasks, 'tasks' => $tasks]);

$layout_content = include_template('layout.php', [
    'projects' => $projects,
    'tasks_quantity' => $tasks_quantity,
	'content' => $page_content,
	'user' => $users['name'],
	'title' => 'Дела в порядке - Главная страница'
]);

print($layout_content);
