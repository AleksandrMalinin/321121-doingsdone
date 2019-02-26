<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set("Europe/Moscow");
setlocale(LC_ALL, 'ru_RU');

require_once('./functions.php');
require_once('./init.php');

// Текущий юзер
$user_id = 3;
$tasks_incoming = 0;

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
    $task = $_POST;

    $required = ['name'];
    $errors = [];

    foreach ($required as $key) {
		if (empty($_POST[$key])) {
            $errors[$key] = 'Заполните это поле';
		}
	}

    $deadline = NULL;
    // если дата установлена
    if (!empty($task['date'])) {
        $date = check_date_format($task['date']);

        if (!$date) {
            $errors['date'] = 'Заполните поле в указанном формате';
        } else if (strtotime($task['date']) < time()) {
            $errors['date'] = 'Дата должна быть больше текущей';
        } else {
            $deadline = date('Y.m.d 23:59:59', strtotime($task['date']));
        }
    }

    $project_id = NULL;
    if ($task['project'] !== 'incoming') {
        // проверяет что задача ссылается на существующий проект
        $project = is_project($connect, $user_id, intval($task['project']));

        if ($project) {
            $project_id = intval($task['project']);
        } else {
            $errors['project'] = 'Такого проекта не существует';
        }
    }

    $file = NULL;
    // проверяет загружен ли файл, даёт ему новое имя, помещает в корень
    if (is_uploaded_file($_FILES['preview']['tmp_name'])) {
        $tmp_name = $_FILES['preview']['tmp_name'];
        $file = $_FILES['preview']['name'];
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $file = uniqid() . "." . $extension;
        move_uploaded_file($tmp_name, '' . $file);
    }

    if (count($errors)) {
    	$page_content = include_template('add.php', [
            'errors' => $errors,
            'projects' => $projects,
            'incoming' => $random_tasks['COUNT(*)'],
            'tasks_all' => $all_tasks['COUNT(*)'],
            'user' => $users['name'],
            'title' => 'Дела в порядке - Добавление задачи'
        ]);
    } else {
        add_task($connect, $task['name'], $user_id, $deadline, $project_id, $file);
        header("Location: /");
    }
} else {
    // Передаём массив с проектами в шаблон
    $page_content = include_template('add.php', [
        'projects' => $projects,
        'incoming' => $random_tasks['COUNT(*)'],
        'tasks_all' => $all_tasks['COUNT(*)'],
        'user' => $users['name'],
        'title' => 'Дела в порядке - Добавление задачи'
    ]);
}

print($page_content);
