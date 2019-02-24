<?php
require_once('./functions.php');
require_once('./init.php');

// Текущий юзер
$user_id = 3;

// Получаем имя текущего пользователя
$users = get_users_data($connect, $user_id);

// Получаем массив с количеством невыполненных заданий
$tasks_quantity = get_tasks_quantity_data($connect, $user_id);

// Получаем массив проектов
$projects = get_projects_data($connect, $user_id, $tasks_quantity);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task = $_POST;

    $required = ['name'];
    $errors = [];

    foreach ($required as $key) {
		if (empty($_POST[$key])) {
            $errors[$key] = 'Заполните это поле';
		}
	}

    // если дата не установлена
    if (empty($task['date'])) {
        $deadline = null;
    // если дата установлена, но она меньше текущей
    } else if (!empty($task['date']) && strtotime($task['date']) < time()) {
        $errors['date'] = 'Дата должна быть больше текущей';
    } else {
        $deadline = $task['date'];
    }

    $project_id = null;
    // проверяет что задача ссылается на существующий проект
    $sql_project = 'SELECT * FROM projects WHERE user_id = ? AND id = ' . $task['project'];
    $project = get_data($connect, $sql_project, $user_id);

    if ($project) {
        $project_id = intval($task['project']);
    }

    var_dump($project);
    var_dump($project_id);

    if (count($errors)) {
    	$page_content = include_template('add.php', [
            'errors' => $errors,
            'projects' => $projects,
            'user' => $users['name'],
            'title' => 'Дела в порядке - Добавление задачи'
        ]);
    } else {
        add_task($connect, $deadline, $task['name'], $user_id, $project_id);
        header("Location: /");
    }
} else {
    // Передаём массив с проектами в шаблон
    $page_content = include_template('add.php', [
        'projects' => $projects,
        'user' => $users['name'],
        'title' => 'Дела в порядке - Добавление задачи'
    ]);
}


print($page_content);
