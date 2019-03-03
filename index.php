<?php
require_once('./functions.php');
require_once('./init.php');

// Показывать или нет выполненные задачи
$show_complete_tasks = 0;

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

// Проверяем был ли передан параметр запроса с id проекта
if (isset($_GET['id'])) {
    // проверяем передана ли строка содержащая число
    if (!is_numeric($_GET['id'])) {
        $project_id = $_GET['id'];
    } else {
        $project_id = intval($_GET['id']);
        $project = is_project($connect, $user_id, $project_id);
    }

    if (!is_numeric($_GET['id']) || $project) {
        $tasks = get_tasks_data($connect, $user_id, $show_complete_tasks, $project_id);
    } else {
        http_response_code(404);
        die();
    }
} else {
    $_GET['id'] = 'all';
}

// Проверяем был ли передан параметр запроса c id задачи
if (isset($_GET['task_id'])) {
    $task_id = intval($_GET['task_id']);
    $task_status = intval($_GET['check']);

    change_task_status($connect, $task_id, $task_status);
}

// Проверяем был ли передан параметр запроса c для покаща всех пвыполненных задач
if (isset($_GET['show_completed'])) {
    $project_id = !is_numeric($_GET['id']) ? $_GET['id'] : intval($_GET['id']);
    $show_complete_tasks = 1;
}

// Получаем массив задач
$tasks = get_tasks_data($connect, $user_id, $show_complete_tasks, $project_id);

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
