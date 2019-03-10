<?php
require_once('./functions.php');
require_once('./init.php');

// Показывать или нет выполненные задачи
$show_complete_tasks = 0;

if (!isset($_GET['id'])) {
    $_GET['id'] = 'all';
}

$project_id = $_GET['id'] ?? NULL;
$task_id = $_GET['task_id'] ?? NULL;
$show_completed = $_GET['show_completed'] ?? NULL;
$term = $_GET['term'] ?? NULL;
$check = $_GET['check'] ?? NULL;
$search = isset($_GET['tasks_search']) && !empty(trim($_GET['tasks_search'])) ? trim($_GET['tasks_search']) : false;

// Получаем имя текущего пользователя
$users = get_users_data($connect, $user_id);

// Проверяем был ли передан параметр запроса с id проекта
if ($project_id) {
    if (is_numeric($project_id)) {
        $project_id = intval($project_id);
        $project = is_project($connect, $user_id, $project_id);

        if (!$project) {
            http_response_code(404);
            die();
        }
    }
}

// Проверяем был ли передан параметр запроса c id задачи
if ($task_id) {
    $task_id = intval($task_id);
    $task_status = intval($check);

    change_task_status($connect, $user_id, $task_id, $task_status);
}

// Проверяем был ли передан параметр запроса c для покаща всех пвыполненных задач
if ($show_completed) {
    $project_id = !is_numeric($project_id) ? $project_id : intval($project_id);
    $show_complete_tasks = 1;
}

// Проверяем был ли передан параметр запроса c типом дэдлайна
if ($term) {
    $project_id = !is_numeric($project_id) ? $project_id : intval($project_id);
}

// Получаем массив с количеством невыполненных заданий
$tasks_quantity = get_tasks_quantity($connect, $user_id);

// Получаем массив проектов
$projects = get_projects_data($connect, $user_id, $tasks_quantity);

// Получаем массив задач
$tasks = get_tasks_data($connect, $user_id, $show_complete_tasks, $project_id, $term, $search);

// Получаем общее количество задач (Все)
$all_tasks = get_tasks_quantity($connect, $user_id, 'all');

// Получаем количество задач без проектов (Входящие)
$random_tasks = get_tasks_quantity($connect, $user_id, 'incoming');

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
