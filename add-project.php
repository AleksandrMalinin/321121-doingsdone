<?php
require_once('./functions.php');
require_once('./init.php');

if (empty($user)) {
    http_response_code(401);
    die();
}

// Получаем имя текущего пользователя
$users = get_users_data($connect, $user_id);

// Получаем массив с количеством невыполненных заданий
$tasks_quantity = get_tasks_quantity($connect, $user_id);

// Получаем массив проектов
$projects = get_projects_data($connect, $user_id, $tasks_quantity);

// Получаем общее количество задач (Все)
$all_tasks = get_tasks_quantity($connect, $user_id, 'all');

// Получаем количество задач без проектов (Входящие)
$random_tasks = get_tasks_quantity($connect, $user_id, 'incoming');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = $_POST;

    $required = ['name'];
    $errors = [];

    if (empty($form['name'])) {
        $errors['name'] = 'Заполните это поле';
	}

    if (!empty($form['name'])) {
        // проверяет что задача ссылается на существующий проект
        $project = is_project($connect, $user_id, $form['name']);

        if ($project) {
            $errors['name'] = 'Такой проект уже существует';
        }
    }

    if (count($errors)) {
    	$page_content = include_template('add-project.php', [
            'errors' => $errors,
            'projects' => $projects,
            'incoming' => $random_tasks['COUNT(*)'],
            'tasks_all' => $all_tasks['COUNT(*)'],
            'user' => $users['name'],
            'title' => 'Дела в порядке - Добавление проекта'
        ]);
    } else {
        add_project($connect, $form['name'], $user_id);
        header("Location: /");
    }
} else {
    // Передаём массив с проектами в шаблон
    $page_content = include_template('add-project.php', [
        'projects' => $projects,
        'incoming' => $random_tasks['COUNT(*)'],
        'tasks_all' => $all_tasks['COUNT(*)'],
        'user' => $users['name'],
        'title' => 'Дела в порядке - Добавление проекта'
    ]);
}

print($page_content);
