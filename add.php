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
$tasks_quantity = get_tasks_quantity_data($connect, $user_id);

// Получаем массив проектов
$projects = get_projects_data($connect, $user_id, $tasks_quantity);

// Получаем общее количество задач (Все)
$all_tasks = get_all_tasks_quantity($connect, $user_id);

// Получаем количество задач без проектов (Входящие)
$random_tasks = get_random_tasks_quantity($connect, $user_id);

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

    // если дата установлена, но она меньше текущей
    if (!empty($task['date']) && strtotime($task['date']) < time()) {
        $errors['date'] = 'Дата должна быть больше текущей';
    }

    $project_id = NULL;

    if ($task['project'] !== 'incoming') {
        // проверяет что задача ссылается на существующий проект
        $sql_project = 'SELECT * FROM projects WHERE user_id = ? AND id = ' . $task['project'];
        $project = get_data($connect, $sql_project, $user_id);

        if ($project) {
            $project_id = intval($task['project']);
        }
    } else {
        $task['project'] = 'incoming';
    }

    // складывает значения в массив
    $data = array($deadline, $task['name'], $user_id, $project_id);

    // получает все значения не равные NULL
    function get_not_null($var) {
        return $var !== NULL;
    }

    $filtered_data = array_filter($data, 'get_not_null');

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
        // add_task($connect, $deadline, $task['name'], $user_id, $project_id);
        add_task($connect, $filtered_data);
        // header("Location: /");
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
